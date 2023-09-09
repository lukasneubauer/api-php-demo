<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Entities\Lesson;
use App\Repositories\LessonRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class DeleteLessonPersister
{
    private EntityManager $em;

    private LessonRepository $lessonRepository;

    public function __construct(
        EntityManager $em,
        LessonRepository $lessonRepository
    ) {
        $this->em = $em;
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteLesson(array $requestData): Lesson
    {
        $lesson = $this->lessonRepository->getById($requestData['id']);
        $this->em->remove($lesson);
        $this->em->flush();

        return $lesson;
    }
}
