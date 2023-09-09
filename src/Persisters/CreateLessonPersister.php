<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\DateTime\GivenTimezoneToUTC;
use App\Entities\Lesson;
use App\EntityFactories\LessonFactory;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class CreateLessonPersister
{
    private ApiToken $apiToken;

    private CourseRepository $courseRepository;

    private DateTimeUTC $dateTimeUTC;

    private EntityManager $em;

    private GivenTimezoneToUTC $givenTimezoneToUTC;

    private LessonFactory $lessonFactory;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        CourseRepository $courseRepository,
        DateTimeUTC $dateTimeUTC,
        EntityManager $em,
        GivenTimezoneToUTC $givenTimezoneToUTC,
        LessonFactory $lessonFactory,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->courseRepository = $courseRepository;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->em = $em;
        $this->givenTimezoneToUTC = $givenTimezoneToUTC;
        $this->lessonFactory = $lessonFactory;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createLesson(array $requestData): Lesson
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $timezone = $user->getTimezone();
        $course = $this->courseRepository->getById($requestData['courseId']);
        $lesson = $this->lessonFactory->create(
            $course,
            $this->givenTimezoneToUTC->convertGivenTimezoneToUTC($timezone, $requestData['from']),
            $this->givenTimezoneToUTC->convertGivenTimezoneToUTC($timezone, $requestData['to']),
            $requestData['name'],
            $now
        );
        $this->em->persist($lesson);
        $this->em->flush();

        return $lesson;
    }
}
