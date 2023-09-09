<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\DeleteCoursePersister;
use App\Repositories\CourseRepository;
use App\Repositories\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class DeleteCoursePersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testDeleteCourse(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $courseId = 'd633abba-9a09-4642-bc27-2ac6698f3463';

            $lessonId = '1b2a40de-6772-4b99-9abf-238cd03054c6';

            /** @var DeleteCoursePersister $deleteCoursePersister */
            $deleteCoursePersister = $dic->get(DeleteCoursePersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);

            EntityManagerCleanup::cleanupEntityManager($dic);

            $deleteCoursePersister->deleteCourse(['id' => $courseId]);

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseId);

            $this->assertNull($courseFromDatabase);

            $lessonFromDatabase = $lessonRepository->getById($lessonId);

            $this->assertNull($lessonFromDatabase);
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
