<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class LessonRepository
{
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityRepository = $em->getRepository(Lesson::class);
    }

    public function getById(string $id): ?Lesson
    {
        return $this->entityRepository->find($id);
    }
}
