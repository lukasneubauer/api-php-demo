<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\UpdateLessonPersister;
use App\Repositories\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class UpdateLessonPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testUpdateLesson(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';
            $lessonId = '1b2a40de-6772-4b99-9abf-238cd03054c6';
            $courseId = '6fd21fb4-5787-4113-9e48-44ded2492608';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var UpdateLessonPersister $updateLessonPersister */
            $updateLessonPersister = $dic->get(UpdateLessonPersister::class);

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);

            $lesson = $lessonRepository->getById($lessonId);
            $lessonUpdatedAt = $lesson->getUpdatedAt();

            $requestData = [
                'name' => 'Minulý, přítomný a budoucí čas 2',
                'from' => '2000-01-01 10:00:00',
                'to' => '2000-01-01 12:00:00',
                'courseId' => $courseId,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $lessonToCheck = $updateLessonPersister->updateLesson($requestData, $lessonId);
            $this->assertSame($lessonId, $lessonToCheck->getId());
            $this->assertSame($courseId, $lessonToCheck->getCourse()->getId());
            $this->assertSame('2000-01-01 09:00:00', $lessonToCheck->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 11:00:00', $lessonToCheck->getTo()->format('Y-m-d H:i:s'));
            $this->assertSame('Minulý, přítomný a budoucí čas 2', $lessonToCheck->getName());
            $this->assertGreaterThan(
                $lessonUpdatedAt->getTimestamp(),
                $lessonToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $lessonFromDatabase = $lessonRepository->getById($lessonToCheck->getId());
            $this->assertSame($lessonId, $lessonFromDatabase->getId());
            $this->assertSame($courseId, $lessonFromDatabase->getCourse()->getId());
            $this->assertSame('2000-01-01 09:00:00', $lessonFromDatabase->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 11:00:00', $lessonFromDatabase->getTo()->format('Y-m-d H:i:s'));
            $this->assertSame('Minulý, přítomný a budoucí čas 2', $lessonFromDatabase->getName());
            $this->assertGreaterThan(
                $lessonUpdatedAt->getTimestamp(),
                $lessonFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
