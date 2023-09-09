<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Course;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CourseRepository
{
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityRepository = $em->getRepository(Course::class);
    }

    public function getById(string $id): ?Course
    {
        return $this->entityRepository->find($id);
    }
}
