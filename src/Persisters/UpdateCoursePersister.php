<?php

declare(strict_types=1);

namespace App\Persisters;

use App\DateTime\DateTimeUTC;
use App\Entities\Course;
use App\EntityFactories\SubjectFactory;
use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use App\Repositories\SubjectRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class UpdateCoursePersister
{
    private ApiToken $apiToken;

    private EntityManager $em;

    private CourseRepository $courseRepository;

    private DateTimeUTC $dateTimeUTC;

    private SessionRepository $sessionRepository;

    private SubjectFactory $subjectFactory;

    private SubjectRepository $subjectRepository;

    public function __construct(
        ApiToken $apiToken,
        EntityManager $em,
        CourseRepository $courseRepository,
        DateTimeUTC $dateTimeUTC,
        SessionRepository $sessionRepository,
        SubjectFactory $subjectFactory,
        SubjectRepository $subjectRepository
    ) {
        $this->apiToken = $apiToken;
        $this->em = $em;
        $this->courseRepository = $courseRepository;
        $this->dateTimeUTC = $dateTimeUTC;
        $this->sessionRepository = $sessionRepository;
        $this->subjectFactory = $subjectFactory;
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateCourse(array $requestData, string $id): Course
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $teacher = $session->getUser();
        $subject = $this->subjectRepository->getByName($requestData['subject']);
        if ($subject === null) {
            $subject = $this->subjectFactory->create($teacher, $requestData['subject'], $now);
            try {
                $this->em->persist($subject);
            } catch (UniqueConstraintViolationException $e) {
            }
        }
        $course = $this->courseRepository->getById($id);
        $course->setName($requestData['name']);
        $course->setSubject($subject);
        $course->setPrice($requestData['price']);
        $course->setIsActive($requestData['isActive']);
        $course->setUpdatedAt($now);
        $this->em->persist($course);
        $this->em->flush();

        return $course;
    }
}
