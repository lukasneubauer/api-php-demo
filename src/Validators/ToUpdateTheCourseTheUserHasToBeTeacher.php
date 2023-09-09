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

class ToUpdateTheCourseTheUserHasToBeTeacher
{
    private SessionRepository $sessionRepository;

    private CourseRepository $courseRepository;

    public function __construct(
        SessionRepository $sessionRepository,
        CourseRepository $courseRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfTheUserIsTeacherToUpdateTheCourse(HeaderBag $headers, ParameterBag $parameters): void
    {
        $course = $this->courseRepository->getById($parameters->get('id'));
        $teacher = $course->getTeacher();
        $apiToken = (string) $headers->get(ApiHeaders::API_TOKEN);
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        if ($teacher->getId() !== $user->getId()) {
            $error = Error::toUpdateTheCourseTheUserHasToBeTeacher();
            $message = \sprintf(Emsg::TO_UPDATE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER);
            throw new ValidationException($error, $message);
        }
    }
}
