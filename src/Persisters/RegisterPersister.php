<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Avatars\AvatarCreator;
use App\Base64\Base64Decoder;
use App\Base64\Base64Encoder;
use App\Database\UniqueKey;
use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\EntityFactories\TokenFactory;
use App\EntityFactories\UserFactory;
use App\Exceptions\CouldNotPersistException;
use App\Exceptions\PasswordHashingFailedException;
use App\Passwords\PasswordEncoderEntityFactory;
use App\PersisterErrors\CouldNotGenerateUniqueValue;
use App\PersisterErrors\ValueIsAlreadyTaken;
use DateTime;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Nette\Utils\ImageException;

class RegisterPersister
{
    /** @var int */
    public const MAX_TRIES = 5;

    /** @var string */
    public const SQL = <<<EOL
INSERT INTO `users` (
    `id`,
    `first_name`,
    `last_name`,
    `avatar`,
    `email`,
    `password_hash`,
    `password_algorithm`,
    `is_teacher`,
    `is_student`,
    `timezone`,
    `token`,
    `token_created_at`,
    `security_code`,
    `security_code_created_at`,
    `security_code_failures`,
    `authentication_failures`,
    `is_locked`,
    `is_active`,
    `created_at`,
    `updated_at`
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
EOL;

    private AvatarCreator $avatarCreator;

    private CouldNotGenerateUniqueValue $couldNotGenerateUniqueValue;

    private Base64Decoder $base64Decoder;

    private Base64Encoder $base64Encoder;

    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private PasswordEncoderEntityFactory $passwordEncoderEntityFactory;

    private TokenFactory $tokenFactory;

    private UniqueKey $uniqueKey;

    private UserFactory $userFactory;

    private ValueIsAlreadyTaken $valueIsAlreadyTaken;

    public function __construct(
        AvatarCreator $avatarCreator,
        CouldNotGenerateUniqueValue $couldNotGenerateUniqueValue,
        Base64Decoder $base64Decoder,
        Base64Encoder $base64Encoder,
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        PasswordEncoderEntityFactory $passwordEncoderEntityFactory,
        TokenFactory $tokenFactory,
        UniqueKey $uniqueKey,
        UserFactory $userFactory,
        ValueIsAlreadyTaken $valueIsAlreadyTaken
    ) {
        $this->avatarCreator = $avatarCreator;
        $this->couldNotGenerateUniqueValue = $couldNotGenerateUniqueValue;
        $this->base64Decoder = $base64Decoder;
        $this->base64Encoder = $base64Encoder;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->passwordEncoderEntityFactory = $passwordEncoderEntityFactory;
        $this->tokenFactory = $tokenFactory;
        $this->uniqueKey = $uniqueKey;
        $this->userFactory = $userFactory;
        $this->valueIsAlreadyTaken = $valueIsAlreadyTaken;
    }

    /**
     * @throws CouldNotPersistException
     * @throws DBALDriverException
     * @throws DBALException
     * @throws ImageException
     * @throws PasswordHashingFailedException
     */
    public function createUser(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();

        if ($requestData['avatar'] !== null) {
            $sourceString = $this->base64Decoder->decode($requestData['avatar']);
            $avatarString = $this->avatarCreator->create($sourceString);
            $base64String = $this->base64Encoder->encode($avatarString);
            $requestData['avatar'] = $base64String;
        }

        $password = $this->passwordEncoderEntityFactory->createPassword($requestData['password']);

        $user = $this->userFactory->create(
            $requestData['firstName'],
            $requestData['lastName'],
            $requestData['avatar'],
            $requestData['email'],
            $password,
            $requestData['timezone'],
            $now
        );

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

        $password = $user->getPassword();
        $token = $user->getToken();

        try {
            $connection = $this->em->getConnection();
            $statement = $connection->prepare(self::SQL);
            $id = $user->getId();
            $statement->bindParam(1, $id);
            $firstName = $user->getFirstName();
            $statement->bindParam(2, $firstName);
            $lastName = $user->getLastName();
            $statement->bindParam(3, $lastName);
            $avatar = $user->getAvatar();
            $statement->bindParam(4, $avatar);
            $email = $user->getEmail();
            $statement->bindParam(5, $email);
            $passwordHash = $password->getHash();
            $statement->bindParam(6, $passwordHash);
            $passwordAlgorithm = $password->getAlgorithm();
            $statement->bindParam(7, $passwordAlgorithm);
            $isTeacher = (int) $user->isTeacher();
            $statement->bindParam(8, $isTeacher);
            $isStudent = (int) $user->isStudent();
            $statement->bindParam(9, $isStudent);
            $timezone = $user->getTimezone();
            $statement->bindParam(10, $timezone);
            $tokenCode = $token->getCode();
            $statement->bindParam(11, $tokenCode);
            $tokenCreatedAt = $token->getCreatedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(12, $tokenCreatedAt);
            $securityCodeCode = null;
            $statement->bindParam(13, $securityCodeCode);
            $securityCodeCreatedAt = null;
            $statement->bindParam(14, $securityCodeCreatedAt);
            $securityCodeInputFailures = 0;
            $statement->bindParam(15, $securityCodeInputFailures);
            $authenticationFailures = $user->getAuthenticationFailures();
            $statement->bindParam(16, $authenticationFailures);
            $isLocked = (int) $user->isLocked();
            $statement->bindParam(17, $isLocked);
            $isActive = (int) $user->isActive();
            $statement->bindParam(18, $isActive);
            $createdAt = $user->getCreatedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(19, $createdAt);
            $updatedAt = $user->getUpdatedAt()->format('Y-m-d H:i:s');
            $statement->bindParam(20, $updatedAt);
            $statement->executeQuery();
        } catch (UniqueConstraintViolationException $e) {
            $uniqueKey = $this->uniqueKey->extractUniqueKeyFromExceptionMessage($e->getMessage());
            if ($uniqueKey === User::UNIQUE_KEY_EMAIL) {
                $this->valueIsAlreadyTaken->throwException('email');
            }
            if ($uniqueKey === User::UNIQUE_KEY_TOKEN) {
                $user->setToken($this->tokenFactory->create($now));
                $this->tryToPersist($user, $now, $callTimes - 1, 'token');
            }
        }

        return $user;
    }
}
