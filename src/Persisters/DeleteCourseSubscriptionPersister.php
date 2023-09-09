<?php

declare(strict_types=1);

namespace App\Persisters;

use App\Entities\Course;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class DeleteCourseSubscriptionPersister
{
    private ApiToken $apiToken;

    private CourseRepository $courseRepository;

    private EntityManager $em;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        CourseRepository $courseRepository,
        EntityManager $em,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->courseRepository = $courseRepository;
        $this->em = $em;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteCourseSubscription(array $requestData): Course
    {
        $course = $this->courseRepository->getById($requestData['courseId']);
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $course->removeStudent($user);
        $this->em->persist($course);
        $this->em->flush();

        return $course;
    }
}
