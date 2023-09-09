<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\Repositories\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class LockAccountPersister
{
    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private UserRepository $userRepository;

    public function __construct(
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        UserRepository $userRepository
    ) {
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function lockAccount(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $user = $this->userRepository->getByEmail($requestData['email']);
        $user->setAuthenticationFailures(User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS);
        $user->setIsLocked(true);
        $user->setUpdatedAt($now);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
