<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Entities\Session;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Sessions\SessionLock;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class LockSessionPersister
{
    private ApiToken $apiToken;

    private EntityManager $em;

    private SessionLock $sessionLock;

    public function __construct(
        ApiToken $apiToken,
        EntityManager $em,
        SessionLock $sessionLock
    ) {
        $this->apiToken = $apiToken;
        $this->em = $em;
        $this->sessionLock = $sessionLock;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function lockSession(): Session
    {
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionLock->lockSession($apiToken);
        $this->em->persist($session);
        $this->em->flush();

        return $session;
    }
}
