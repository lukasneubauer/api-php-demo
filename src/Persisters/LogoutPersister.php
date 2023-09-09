<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Entities\Session;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Repositories\SessionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class LogoutPersister
{
    private ApiToken $apiToken;

    private EntityManager $em;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        EntityManager $em,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->em = $em;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteSession(): Session
    {
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $this->em->remove($session);
        $this->em->flush();

        return $session;
    }
}
