<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Persisters\DeleteLessonPersister;
use App\Repositories\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class DeleteLessonPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testDeleteLesson(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $lessonId = '1b2a40de-6772-4b99-9abf-238cd03054c6';

            /** @var DeleteLessonPersister $deleteLessonPersister */
            $deleteLessonPersister = $dic->get(DeleteLessonPersister::class);

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);

            EntityManagerCleanup::cleanupEntityManager($dic);

            $deleteLessonPersister->deleteLesson(['id' => $lessonId]);

            EntityManagerCleanup::cleanupEntityManager($dic);

            $lessonFromDatabase = $lessonRepository->getById($lessonId);

            $this->assertNull($lessonFromDatabase);
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
