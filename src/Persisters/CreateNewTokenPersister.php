<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Database\UniqueKey;
use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\EntityFactories\TokenFactory;
use App\Exceptions\CouldNotPersistException;
use App\PersisterErrors\CouldNotGenerateUniqueValue;
use App\Repositories\UserRepository;
use DateTime;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;

class CreateNewTokenPersister
{
    /** @var int */
    public const MAX_TRIES = 5;

    /** @var string */
    public const SQL = <<<EOL
UPDATE `users`
SET `token` = ?,
    `token_created_at` = ?,
    `updated_at` = ?
WHERE `id` = ?
EOL;

    private CouldNotGenerateUniqueValue $couldNotGenerateUniqueValue;

    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private TokenFactory $tokenFactory;

    private UniqueKey $uniqueKey;

    private UserRepository $userRepository;

    public function __construct(
        CouldNotGenerateUniqueValue $couldNotGenerateUniqueValue,
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        TokenFactory $tokenFactory,
        UniqueKey $uniqueKey,
        UserRepository $userRepository
    ) {
        $this->couldNotGenerateUniqueValue = $couldNotGenerateUniqueValue;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->tokenFactory = $tokenFactory;
        $this->uniqueKey = $uniqueKey;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws CouldNotPersistException
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function createNewToken(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $user = $this->userRepository->getByEmail($requestData['email']);
        $user->setToken($this->tokenFactory->create($now));
        $user->setUpdatedAt($now);

        return $this->tryToPersist($user, $now);
    }

    /**
     * @throws CouldNotPersistException
     * @throws DBALDriverException
     * @throws DBALException
     */
    private function tryToPersist(
        User $user,
        DateTime $now,
        int $callTimes = self::MAX_TRIES,
        ?string $uniqueProperty = null
    ): User {
        if ($callTimes === 0) {
            $this->couldNotGenerateUniqueValue->throwException($uniqueProperty, self::MAX_TRIES);
        }

        $token = $user->getToken();

        try {
            $connection = $this->em->getConnection();
            $statement = $connection->prepare(self::SQL);
            $tokenCode = $token->getCode();
            $statement->bindParam(1, $tokenCode);
            $tokenCreatedAt = $token->getCreatedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(2, $tokenCreatedAt);
            $updatedAt = $user->getUpdatedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(3, $updatedAt);
            $id = $user->getId();
            $statement->bindParam(4, $id);
            $statement->executeQuery();
        } catch (UniqueConstraintViolationException $e) {
            $uniqueKey = $this->uniqueKey->extractUniqueKeyFromExceptionMessage($e->getMessage());
            if ($uniqueKey === User::UNIQUE_KEY_TOKEN) {
                $user->setToken($this->tokenFactory->create($now));
                $this->tryToPersist($user, $now, $callTimes - 1, 'token');
            }
        }

        return $user;
    }
}
