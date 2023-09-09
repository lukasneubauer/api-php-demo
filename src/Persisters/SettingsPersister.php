<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Database\UniqueKey;
use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\CouldNotPersistException;
use App\Exceptions\NoApiTokenFoundException;
use App\Exceptions\PasswordHashingFailedException;
use App\Http\ApiToken;
use App\Passwords\PasswordEncoderEntityFactory;
use App\PersisterErrors\ValueIsAlreadyTaken;
use App\Repositories\SessionRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class SettingsPersister
{
    private ApiToken $apiToken;

    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private PasswordEncoderEntityFactory $passwordEncoderEntityFactory;

    private SessionRepository $sessionRepository;

    private UniqueKey $uniqueKey;

    private ValueIsAlreadyTaken $valueIsAlreadyTaken;

    public function __construct(
        ApiToken $apiToken,
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        PasswordEncoderEntityFactory $passwordEncoderEntityFactory,
        SessionRepository $sessionRepository,
        UniqueKey $uniqueKey,
        ValueIsAlreadyTaken $valueIsAlreadyTaken
    ) {
        $this->apiToken = $apiToken;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->passwordEncoderEntityFactory = $passwordEncoderEntityFactory;
        $this->sessionRepository = $sessionRepository;
        $this->uniqueKey = $uniqueKey;
        $this->valueIsAlreadyTaken = $valueIsAlreadyTaken;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws CouldNotPersistException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws PasswordHashingFailedException
     */
    public function updateSettings(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $user->setFirstName($requestData['firstName']);
        $user->setLastName($requestData['lastName']);
        $user->setEmail($requestData['email']);
        if ($requestData['password'] !== null) {
            $password = $this->passwordEncoderEntityFactory->createPassword($requestData['password']);
            $user->setPassword($password);
        }
        $user->setIsTeacher($requestData['isTeacher']);
        $user->setTimezone($requestData['timezone']);
        $user->setUpdatedAt($now);

        try {
            $this->em->persist($user);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $uniqueKey = $this->uniqueKey->extractUniqueKeyFromExceptionMessage($e->getMessage());
            if ($uniqueKey === User::UNIQUE_KEY_EMAIL) {
                $this->valueIsAlreadyTaken->throwException('email');
            }
        }

        return $user;
    }
}
