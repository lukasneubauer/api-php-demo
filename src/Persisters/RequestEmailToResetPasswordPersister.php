<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\EntityFactories\TokenFactory;
use App\Repositories\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class RequestEmailToResetPasswordPersister
{
    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private TokenFactory $tokenFactory;

    private UserRepository $userRepository;

    public function __construct(
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        TokenFactory $tokenFactory,
        UserRepository $userRepository
    ) {
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->tokenFactory = $tokenFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function requestEmailToResetPassword(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $user = $this->userRepository->getByEmail($requestData['email']);
        $token = $this->tokenFactory->create($now);
        $user->setToken($token);
        $user->setUpdatedAt($now);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
