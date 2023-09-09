<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Repositories\SessionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class DeleteAvatarPersister
{
    private ApiToken $apiToken;

    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAvatar(): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $user->unsetAvatar();
        $user->setUpdatedAt($now);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
