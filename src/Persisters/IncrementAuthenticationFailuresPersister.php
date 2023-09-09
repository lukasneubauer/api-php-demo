<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\Repositories\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class IncrementAuthenticationFailuresPersister
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
    public function incrementAuthenticationFailures(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $user = $this->userRepository->getByEmail($requestData['email']);
        $user->setAuthenticationFailures($user->getAuthenticationFailures() + 1);
        $user->setUpdatedAt($now);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
