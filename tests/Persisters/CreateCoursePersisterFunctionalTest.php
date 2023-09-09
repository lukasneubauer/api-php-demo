<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\CreateCoursePersister;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class CreateCoursePersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testCreateCourseWhereUserIsTeacherUsesExistingSubject(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var CreateCoursePersister $createCoursePersister */
            $createCoursePersister = $dic->get(CreateCoursePersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $session = $sessionRepository->getByApiToken($apiToken);

            $user = $session->getUser();

            $userCreatedAt = $user->getCreatedAt();
            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'name' => 'Letní doučování angličtiny',
                'subject' => 'Anglický jazyk',
                'price' => 25000,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseToCheck = $createCoursePersister->createCourse($requestData);

            $this->assertSame($courseToCheck->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseToCheck->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseToCheck->getName());
            $this->assertSame(25000, $courseToCheck->getPrice());
            $this->assertFalse($courseToCheck->isActive());
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Anglický jazyk', $courseToCheck->getSubject()->getName());
            $this->assertSame(
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertNotSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseToCheck->getId());

            $this->assertSame($courseFromDatabase->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseFromDatabase->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseFromDatabase->getName());
            $this->assertSame(25000, $courseFromDatabase->getPrice());
            $this->assertFalse($courseFromDatabase->isActive());
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Anglický jazyk', $courseFromDatabase->getSubject()->getName());
            $this->assertSame(
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertNotSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
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
    public function testCreateCourseWhereUserIsTeacherCreatesNewSubject(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'pxlph3gpe4jaaz87zfmgwq4bhiqcj2xofgrxqndtfssvtjap5od2rxuhivm2htpzg548cff9j6wdr2es';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var CreateCoursePersister $createCoursePersister */
            $createCoursePersister = $dic->get(CreateCoursePersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $session = $sessionRepository->getByApiToken($apiToken);

            $user = $session->getUser();

            $userCreatedAt = $user->getCreatedAt();
            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'name' => 'Letní doučování angličtiny',
                'subject' => 'Testing Subject',
                'price' => 25000,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseToCheck = $createCoursePersister->createCourse($requestData);

            $this->assertSame($courseToCheck->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseToCheck->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseToCheck->getName());
            $this->assertSame(25000, $courseToCheck->getPrice());
            $this->assertFalse($courseToCheck->isActive());
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Testing Subject', $courseToCheck->getSubject()->getName());
            $this->assertSame(
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseToCheck->getId());

            $this->assertSame($courseFromDatabase->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseFromDatabase->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseFromDatabase->getName());
            $this->assertSame(25000, $courseFromDatabase->getPrice());
            $this->assertFalse($courseFromDatabase->isActive());
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Testing Subject', $courseFromDatabase->getSubject()->getName());
            $this->assertSame(
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
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
    public function testCreateCourseWhereUserIsStudentUsesExistingSubject(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'jkgc66bbpz1a82fjyxsetm7ztgxd5jbq4l7s5rmsotogayonbjxr7ubqsp5ar93ch6oeji1it03k3494';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var CreateCoursePersister $createCoursePersister */
            $createCoursePersister = $dic->get(CreateCoursePersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $session = $sessionRepository->getByApiToken($apiToken);

            $user = $session->getUser();

            $userCreatedAt = $user->getCreatedAt();
            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'name' => 'Letní doučování angličtiny',
                'subject' => 'Anglický jazyk',
                'price' => 25000,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseToCheck = $createCoursePersister->createCourse($requestData);

            $this->assertSame($courseToCheck->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseToCheck->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseToCheck->getName());
            $this->assertSame(25000, $courseToCheck->getPrice());
            $this->assertFalse($courseToCheck->isActive());
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Anglický jazyk', $courseToCheck->getSubject()->getName());
            $this->assertSame(
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertNotSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseToCheck->getId());

            $this->assertSame($courseFromDatabase->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseFromDatabase->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseFromDatabase->getName());
            $this->assertSame(25000, $courseFromDatabase->getPrice());
            $this->assertFalse($courseFromDatabase->isActive());
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Anglický jazyk', $courseFromDatabase->getSubject()->getName());
            $this->assertSame(
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertNotSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertNotSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
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
    public function testCreateCourseWhereUserIsStudentCreatesNewSubject(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'jkgc66bbpz1a82fjyxsetm7ztgxd5jbq4l7s5rmsotogayonbjxr7ubqsp5ar93ch6oeji1it03k3494';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var CreateCoursePersister $createCoursePersister */
            $createCoursePersister = $dic->get(CreateCoursePersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            $session = $sessionRepository->getByApiToken($apiToken);

            $user = $session->getUser();

            $userCreatedAt = $user->getCreatedAt();
            $userUpdatedAt = $user->getUpdatedAt();

            $requestData = [
                'name' => 'Letní doučování angličtiny',
                'subject' => 'Testing Subject',
                'price' => 25000,
            ];

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseToCheck = $createCoursePersister->createCourse($requestData);

            $this->assertSame($courseToCheck->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseToCheck->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseToCheck->getName());
            $this->assertSame(25000, $courseToCheck->getPrice());
            $this->assertFalse($courseToCheck->isActive());
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Testing Subject', $courseToCheck->getSubject()->getName());
            $this->assertSame(
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseToCheck->getCreatedAt()->getTimestamp(),
                $courseToCheck->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseToCheck->getId());

            $this->assertSame($courseFromDatabase->getTeacher()->getId(), $user->getId());
            $this->assertTrue($courseFromDatabase->getTeacher()->isTeacher());
            $this->assertSame(
                $userCreatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getCreatedAt()->getTimestamp()
            );
            $this->assertGreaterThan(
                $userUpdatedAt->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Letní doučování angličtiny', $courseFromDatabase->getName());
            $this->assertSame(25000, $courseFromDatabase->getPrice());
            $this->assertFalse($courseFromDatabase->isActive());
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame('Testing Subject', $courseFromDatabase->getSubject()->getName());
            $this->assertSame(
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );

            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getCreatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getSubject()->getUpdatedAt()->getTimestamp()
            );
            $this->assertSame(
                $courseFromDatabase->getCreatedAt()->getTimestamp(),
                $courseFromDatabase->getTeacher()->getUpdatedAt()->getTimestamp()
            );
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
