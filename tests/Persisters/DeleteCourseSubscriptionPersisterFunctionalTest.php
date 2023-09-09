<?php

declare(strict_types=1);

namespace Tests\App\Persisters;

use App\Http\ApiHeaders;
use App\Persisters\DeleteCourseSubscriptionPersister;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use App\Repositories\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Database;
use Tests\EntityManagerCleanup;
use Throwable;

final class DeleteCourseSubscriptionPersisterFunctionalTest extends KernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testDeleteCourseSubscription(): void
    {
        static::bootKernel();

        $dic = static::getContainer();

        try {
            $apiToken = 'jkgc66bbpz1a82fjyxsetm7ztgxd5jbq4l7s5rmsotogayonbjxr7ubqsp5ar93ch6oeji1it03k3494';
            $courseId = 'd633abba-9a09-4642-bc27-2ac6698f3463';

            $request = new Request();
            $request->headers->set(ApiHeaders::API_TOKEN, $apiToken);

            /** @var RequestStack $requestStack */
            $requestStack = $dic->get(RequestStack::class);
            $requestStack->push($request);

            /** @var DeleteCourseSubscriptionPersister $deleteCourseSubscriptionPersister */
            $deleteCourseSubscriptionPersister = $dic->get(DeleteCourseSubscriptionPersister::class);

            /** @var CourseRepository $courseRepository */
            $courseRepository = $dic->get(CourseRepository::class);

            /** @var SessionRepository $sessionRepository */
            $sessionRepository = $dic->get(SessionRepository::class);

            /** @var UserRepository $userRepository */
            $userRepository = $dic->get(UserRepository::class);

            $course = $courseRepository->getById($courseId);

            $subscriptionCount = \count($course->getStudents());

            $session = $sessionRepository->getByApiToken($apiToken);

            $user = $session->getUser();

            $userUpdatedAt = $user->getUpdatedAt();

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseToCheck = $deleteCourseSubscriptionPersister->deleteCourseSubscription(['courseId' => $courseId]);
            $this->assertCount($subscriptionCount - 1, $courseToCheck->getStudents());

            $userToCheck = $userRepository->getById($user->getId());
            $this->assertTrue($userToCheck->isStudent());
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $userToCheck->getUpdatedAt()->getTimestamp()
            );

            $this->assertFalse($courseToCheck->getStudents()->contains($userToCheck));

            EntityManagerCleanup::cleanupEntityManager($dic);

            $courseFromDatabase = $courseRepository->getById($courseToCheck->getId());
            $this->assertCount($subscriptionCount - 1, $courseFromDatabase->getStudents());

            $userFromDatabase = $userRepository->getById($userToCheck->getId());
            $this->assertTrue($userFromDatabase->isStudent());
            $this->assertSame(
                $userUpdatedAt->getTimestamp(),
                $userFromDatabase->getUpdatedAt()->getTimestamp()
            );

            $this->assertFalse($courseFromDatabase->getStudents()->contains($userFromDatabase));
        } catch (Throwable $e) {
            throw $e;
        } finally {
            Database::resetDatabase($dic);
        }
    }
}
