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

class CannotSubscribeToYourOwnCourse
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
    public function checkIfCannotSubscribeToYourOwnCourse(HeaderBag $headers, array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));
        if ($course->getTeacher()->getId() === $session->getUser()->getId()) {
            $error = Error::cannotSubscribeToYourOwnCourse();
            $message = \sprintf(Emsg::CANNOT_SUBSCRIBE_TO_YOUR_OWN_COURSE);
            throw new ValidationException($error, $message);
        }
    }
}
