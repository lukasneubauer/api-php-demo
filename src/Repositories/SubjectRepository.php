<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Subject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SubjectRepository
{
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityRepository = $em->getRepository(Subject::class);
    }

    /**
     * @return Subject[]
     */
    public function getAll(): array
    {
        return $this->entityRepository->findBy([], ['name' => 'ASC']);
    }

    public function getByName(string $name): ?Subject
    {
        return $this->entityRepository->findOneBy(['name' => $name]);
    }
}
