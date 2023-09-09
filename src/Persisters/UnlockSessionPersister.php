<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\Entities\Session;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiClientIdFoundException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiClientId;
use App\Http\ApiToken;
use App\Passwords\PasswordRehasher;
use App\Repositories\SessionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class UnlockSessionPersister
{
    private ApiClientId $apiClientId;

    private ApiToken $apiToken;

    private EntityManager $em;

    private DateTimeUTC $dateTimeUTC;

    private PasswordRehasher $passwordRehasher;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiClientId $apiClientId,
        ApiToken $apiToken,
        EntityManager $em,
        DateTimeUTC $dateTimeUTC,
        PasswordRehasher $passwordRehasher,
        SessionRepository $sessionRepository
    ) {
        $this->apiClientId = $apiClientId;
        $this->apiToken = $apiToken;
        $this->em = $em;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->passwordRehasher = $passwordRehasher;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiClientIdFoundException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function unlockSession(array $requestData): Session
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $apiClientId = $this->apiClientId->getApiClientId();
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $session->setApiClientId($apiClientId);
        $session->setIsLocked(false);
        $session->setUpdatedAt($now);
        $user = $session->getUser();
        $password = $this->passwordRehasher->rehashPassword($requestData['password'], $user->getPassword());
        $user->setPassword($password);
        $user->setAuthenticationFailures(0);
        $user->setUpdatedAt($now);
        $this->em->persist($session);
        $this->em->persist($user);
        $this->em->flush();

        return $session;
    }
}
