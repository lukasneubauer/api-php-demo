<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class TryingToGetLessonListForCourseWhichIsNotYours
{
    private CourseRepository $courseRepository;

    private SessionRepository $sessionRepository;

    public function __construct(
        CourseRepository $courseRepository,
        SessionRepository $sessionRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfTryingToGetLessonListForCourseWhichIsNotYours(HeaderBag $headers, ParameterBag $parameters): void
    {
        $course = $this->courseRepository->getById($parameters->get('id'));
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));

        if ($session->getUser()->getId() !== $course->getTeacher()->getId()) {
            $error = Error::tryingToGetLessonListForCourseWhichIsNotYours();
            $message = Emsg::TRYING_TO_GET_LESSON_LIST_FOR_COURSE_WHICH_IS_NOT_YOURS;
            throw new ValidationException($error, $message);
        }
    }
}
