<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\DateTime\GivenTimezoneToUTC;
use App\Entities\Lesson;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Repositories\CourseRepository;
use App\Repositories\LessonRepository;
use App\Repositories\SessionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class UpdateLessonPersister
{
    private ApiToken $apiToken;

    private EntityManager $em;

    private CourseRepository $courseRepository;

    private DateTimeUTC $dateTimeUTC;

    private GivenTimezoneToUTC $givenTimezoneToUTC;

    private LessonRepository $lessonRepository;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        EntityManager $em,
        CourseRepository $courseRepository,
        DateTimeUTC $dateTimeUTC,
        GivenTimezoneToUTC $givenTimezoneToUTC,
        LessonRepository $lessonRepository,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->em = $em;
        $this->courseRepository = $courseRepository;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->givenTimezoneToUTC = $givenTimezoneToUTC;
        $this->lessonRepository = $lessonRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateLesson(array $requestData, string $id): Lesson
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $timezone = $user->getTimezone();
        $course = $this->courseRepository->getById($requestData['courseId']);
        $lesson = $this->lessonRepository->getById($id);
        $lesson->setName($requestData['name']);
        $lesson->setCourse($course);
        $lesson->setFrom($this->givenTimezoneToUTC->convertGivenTimezoneToUTC($timezone, $requestData['from']));
        $lesson->setTo($this->givenTimezoneToUTC->convertGivenTimezoneToUTC($timezone, $requestData['to']));
        $lesson->setUpdatedAt($now);
        $this->em->persist($lesson);
        $this->em->flush();

        return $lesson;
    }
}
