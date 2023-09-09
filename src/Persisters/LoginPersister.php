<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Database\UniqueKey;
use App\DateTime\DateTimeUTC;
use App\Entities\Password;
use App\Entities\Session;
use App\EntityFactories\SessionFactory;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\CouldNotPersistException;
use App\Exceptions\NoApiClientIdFoundException;
use App\Exceptions\PasswordHashingFailedException;
use App\Http\ApiClientId;
use App\Passwords\PasswordRehasher;
use App\PersisterErrors\CouldNotGenerateUniqueValue;
use App\Repositories\UserRepository;
use DateTime;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;

class LoginPersister
{
    /** @var int */
    public const MAX_TRIES = 5;

    /** @var string */
    public const INSERT_INTO_SESSIONS_SQL = <<<EOL
INSERT INTO `sessions` (
    `id`,
    `user_id`,
    `api_client_id`,
    `old_api_token`,
    `current_api_token`,
    `refreshed_at`,
    `is_locked`,
    `created_at`,
    `updated_at`
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
EOL;

    /** @var string */
    public const UPDATE_USERS_SET_PASSWORD_SQL = <<<EOL
UPDATE `users`
SET `password_hash` = ?,
    `password_algorithm` = ?,
    `updated_at` = ?
WHERE `id` = ?
EOL;

    /** @var string */
    public const UPDATE_USERS_SET_AUTHENTICATION_FAILURES_SQL = <<<EOL
UPDATE `users`
SET `authentication_failures` = ?,
    `updated_at` = ?
WHERE `id` = ?
EOL;

    private ApiClientId $apiClientId;

    private CouldNotGenerateUniqueValue $couldNotGenerateUniqueValue;

    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private PasswordRehasher $passwordRehasher;

    private SessionFactory $sessionFactory;

    private UniqueKey $uniqueKey;

    private UserRepository $userRepository;

    public function __construct(
        ApiClientId $apiClientId,
        CouldNotGenerateUniqueValue $couldNotGenerateUniqueValue,
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        PasswordRehasher $passwordRehasher,
        SessionFactory $sessionFactory,
        UniqueKey $uniqueKey,
        UserRepository $userRepository
    ) {
        $this->apiClientId = $apiClientId;
        $this->couldNotGenerateUniqueValue = $couldNotGenerateUniqueValue;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->passwordRehasher = $passwordRehasher;
        $this->sessionFactory = $sessionFactory;
        $this->uniqueKey = $uniqueKey;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws CouldNotPersistException
     * @throws DBALDriverException
     * @throws DBALException
     * @throws NoApiClientIdFoundException
     * @throws PasswordHashingFailedException
     */
    public function createSession(array $requestData): Session
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $user = $this->userRepository->getByEmail($requestData['email']);
        $apiClientId = $this->apiClientId->getApiClientId();
        $session = $this->sessionFactory->create($user, $apiClientId, $now);
        $rehashedPassword = $this->passwordRehasher->rehashPassword($requestData['password'], $user->getPassword());

        return $this->tryToPersist($session, $rehashedPassword, $now);
    }

    /**
     * @throws CouldNotPersistException
     * @throws DBALDriverException
     * @throws DBALException
     */
    private function tryToPersist(
        Session $session,
        Password $rehashedPassword,
        DateTime $now,
        int $callTimes = self::MAX_TRIES,
        ?string $uniqueProperty = null
    ): Session {
        if ($callTimes === 0) {
            $this->couldNotGenerateUniqueValue->throwException($uniqueProperty, self::MAX_TRIES);
        }

        $connection = $this->em->getConnection();
        $connection->beginTransaction();

        $user = $session->getUser();

        try {
            $statement = $connection->prepare(self::INSERT_INTO_SESSIONS_SQL);
            $id = $session->getId();
            $statement->bindParam(1, $id);
            $userId = $user->getId();
            $statement->bindParam(2, $userId);
            $apiClientId = $session->getApiClientId();
            $statement->bindParam(3, $apiClientId);
            $oldApiToken = null;
            $statement->bindParam(4, $oldApiToken);
            $currentApiToken = $session->getCurrentApiToken();
            $statement->bindParam(5, $currentApiToken);
            $refreshedAt = $session->getRefreshedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(6, $refreshedAt);
            $isLocked = 0;
            $statement->bindParam(7, $isLocked);
            $createdAt = $session->getCreatedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(8, $createdAt);
            $updatedAt = $session->getUpdatedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(9, $updatedAt);
            $statement->executeQuery();

            if ($session->getUser()->getPassword()->getHash() !== $rehashedPassword->getHash()) {
                $user->setPassword($rehashedPassword);
                $user->setUpdatedAt($now);
                $statement = $connection->prepare(self::UPDATE_USERS_SET_PASSWORD_SQL);
                $passwordHash = $rehashedPassword->getHash();
                $statement->bindParam(1, $passwordHash);
                $passwordAlgorithm = $rehashedPassword->getAlgorithm();
                $statement->bindParam(2, $passwordAlgorithm);
                $updatedAt = $user->getUpdatedAt()->format('Y-m-d H:i:s');
                $statement->bindParam(3, $updatedAt);
                $userId = $user->getId();
                $statement->bindParam(4, $userId);
                $statement->executeQuery();
            }

            if ($user->getAuthenticationFailures() > 0) {
                $user->setAuthenticationFailures(0);
                $user->setUpdatedAt($now);
                $statement = $connection->prepare(self::UPDATE_USERS_SET_AUTHENTICATION_FAILURES_SQL);
                $authenticationFailures = 0;
                $statement->bindParam(1, $authenticationFailures);
                $updatedAt = $user->getUpdatedAt()->format('Y-m-d H:i:s');
                $statement->bindParam(2, $updatedAt);
                $userId = $user->getId();
                $statement->bindParam(3, $userId);
                $statement->executeQuery();
            }

            $connection->commit();
        } catch (UniqueConstraintViolationException $e) {
            $connection->rollBack();

            $uniqueKey = $this->uniqueKey->extractUniqueKeyFromExceptionMessage($e->getMessage());
            if ($uniqueKey === Session::UNIQUE_KEY_CURRENT_API_TOKEN) {
                $session = $this->sessionFactory->create($session->getUser(), $session->getApiClientId(), $now);
                $this->tryToPersist($session, $rehashedPassword, $now, $callTimes - 1, 'apiToken');
            }
        }

        return $session;
    }
}
