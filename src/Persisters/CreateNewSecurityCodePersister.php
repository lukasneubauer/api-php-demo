<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\EntityFactories\SecurityCodeFactory;
use App\Repositories\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class CreateNewSecurityCodePersister
{
    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private SecurityCodeFactory $securityCodeFactory;

    private UserRepository $userRepository;

    public function __construct(
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        SecurityCodeFactory $securityCodeFactory,
        UserRepository $userRepository
    ) {
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->securityCodeFactory = $securityCodeFactory;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createNewSecurityCode(array $requestData): User
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $user = $this->userRepository->getByEmail($requestData['email']);
        $securityCode = $this->securityCodeFactory->create($now);
        $user->setSecurityCode($securityCode);
        $user->setUpdatedAt($now);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
