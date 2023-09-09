<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository
{
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityRepository = $em->getRepository(User::class);
    }

    /**
     * @return User[]
     */
    public function getAllTeachers(): array
    {
        return $this->entityRepository->findBy(['isTeacher' => true, 'isActive' => true], ['lastName' => 'ASC', 'firstName' => 'ASC']);
    }

    public function getById(string $id): ?User
    {
        return $this->entityRepository->find($id);
    }

    public function getByEmail(string $email): ?User
    {
        return $this->entityRepository->findOneBy(['email' => $email]);
    }

    public function getByToken(string $token): ?User
    {
        return $this->entityRepository->findOneBy(['token.code' => $token]);
    }

    public function getByEmailAndToken(string $email, string $token): ?User
    {
        return $this->entityRepository->findOneBy([
            'email' => $email,
            'token.code' => $token,
        ]);
    }
}
