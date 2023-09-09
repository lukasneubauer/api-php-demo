<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\UpdateCoursePersister;
use App\Repositories\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class UpdateCoursePersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testUpdateCourseUsesExistingSubject(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';
            $courseId = 'd633abba-9a09-4642-bc27-2ac6698f3463';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var UpdateCoursePersister $updateCoursePersister */
            $updateCoursePersister = $dic->get(UpdateCoursePersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            $course = $courseRepository->getById($courseId);
            $courseUpdatedAt = $course->getUpdatedAt();

            $requestData = [
                'name' => 'Letní doučování angličtiny 2',
                'subject' => 'Anglický jazyk',
                'price' => 50000,
                'isActive' => true,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseToCheck = $updateCoursePersister->updateCourse($requestData, $courseId);
            $this->assertSame('Letní doučování angličtiny 2', $courseToCheck->getName());
            $this->assertSame('Anglický jazyk', $courseToCheck->getSubject()->getName());
            $this->assertNotSame(
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(50000, $courseToCheck->getPrice());
            $this->assertTrue($courseToCheck->isActive());
            $this->assertGreaterThan(
                $courseUpdatedAt->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseToCheck->getId());
            $this->assertSame('Letní doučování angličtiny 2', $courseFromDatabase->getName());
            $this->assertSame('Anglický jazyk', $courseFromDatabase->getSubject()->getName());
            $this->assertNotSame(
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(50000, $courseFromDatabase->getPrice());
            $this->assertTrue($courseFromDatabase->isActive());
            $this->assertGreaterThan(
                $courseUpdatedAt->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }

    /**
     * @throws Throwable
     */
    public function testUpdateCourseCreatesNewSubject(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';
            $courseId = 'd633abba-9a09-4642-bc27-2ac6698f3463';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var UpdateCoursePersister $updateCoursePersister */
            $updateCoursePersister = $dic->get(UpdateCoursePersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            $course = $courseRepository->getById($courseId);
            $courseUpdatedAt = $course->getUpdatedAt();

            $requestData = [
                'name' => 'Letní doučování angličtiny 2',
                'subject' => 'Testing Subject',
                'price' => 50000,
                'isActive' => true,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseToCheck = $updateCoursePersister->updateCourse($requestData, $courseId);
            $this->assertSame('Letní doučování angličtiny 2', $courseToCheck->getName());
            $this->assertSame('Testing Subject', $courseToCheck->getSubject()->getName());
            $this->assertSame(
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(50000, $courseToCheck->getPrice());
            $this->assertTrue($courseToCheck->isActive());
            $this->assertGreaterThan(
                $courseUpdatedAt->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseToCheck->getId());
            $this->assertSame('Letní doučování angličtiny 2', $courseFromDatabase->getName());
            $this->assertSame('Testing Subject', $courseFromDatabase->getSubject()->getName());
            $this->assertSame(
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(50000, $courseFromDatabase->getPrice());
            $this->assertTrue($courseFromDatabase->isActive());
            $this->assertGreaterThan(
                $courseUpdatedAt->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
