<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;

class CannotSubscribeToInactiveCourse
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotSubscribeToInactiveCourse(array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        if ($course->isActive() === false) {
            $error = Error::cannotSubscribeToInactiveCourse();
            $message = \sprintf(Emsg::CANNOT_SUBSCRIBE_TO_INACTIVE_COURSE);
            throw new ValidationException($error, $message);
        }
    }
}
