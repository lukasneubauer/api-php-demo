<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Entities\Course;
use App\Repositories\CourseRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class DeleteCoursePersister
{
    private CourseRepository $courseRepository;

    private EntityManager $em;

    public function __construct(
        CourseRepository $courseRepository,
        EntityManager $em
    ) {
        $this->courseRepository = $courseRepository;
        $this->em = $em;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteCourse(array $requestData): Course
    {
        $course = $this->courseRepository->getById($requestData['id']);

        foreach ($course->getLessons() as $lesson) {
            $this->em->remove($lesson);
        }

        $this->em->remove($course);
        $this->em->flush();

        return $course;
    }
}
