<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\CreateLessonPersister;
use App\Repositories\LessonRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class CreateLessonPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testCreateLesson(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';
            $courseId = '6fd21fb4-5787-4113-9e48-44ded2492608';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var CreateLessonPersister $createLessonPersister */
            $createLessonPersister = $dic->get(CreateLessonPersister::class);

            /** @var LessonRepository $lessonRepository */
            $lessonRepository = $dic->get(LessonRepository::class);

            $requestData = [
                'name' => 'Minulý, přítomný a budoucí čas',
                'from' => '2000-01-01 14:00:00',
                'to' => '2000-01-01 16:00:00',
                'courseId' => $courseId,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $lessonToCheck = $createLessonPersister->createLesson($requestData);
            $this->assertNotNull($lessonToCheck->getId());
            $this->assertSame($courseId, $lessonToCheck->getCourse()->getId());
            $this->assertSame('2000-01-01 13:00:00', $lessonToCheck->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 15:00:00', $lessonToCheck->getTo()->format('Y-m-d H:i:s'));
            $this->assertSame('Minulý, přítomný a budoucí čas', $lessonToCheck->getName());
            $this->assertSame(
                $lessonToCheck->getCreatedAt()->getTimestamp(),
                $lessonToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $lessonFromDatabase = $lessonRepository->getById($lessonToCheck->getId());
            $this->assertNotNull($lessonFromDatabase->getId());
            $this->assertSame($courseId, $lessonFromDatabase->getCourse()->getId());
            $this->assertSame('2000-01-01 13:00:00', $lessonFromDatabase->getFrom()->format('Y-m-d H:i:s'));
            $this->assertSame('2000-01-01 15:00:00', $lessonFromDatabase->getTo()->format('Y-m-d H:i:s'));
            $this->assertSame('Minulý, přítomný a budoucí čas', $lessonFromDatabase->getName());
            $this->assertSame(
                $lessonFromDatabase->getCreatedAt()->getTimestamp(),
                $lessonFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
