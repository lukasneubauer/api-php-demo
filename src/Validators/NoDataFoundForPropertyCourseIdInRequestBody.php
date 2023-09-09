<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;

class NoDataFoundForPropertyCourseIdInRequestBody
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForPropertyCourseIdWereFound(array $data): void
    {
        $course = $this->courseRepository->getById($data['id']);
        if ($course === null) {
            $error = Error::noDataFoundForPropertyInRequestBody('id');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'id');
            throw new ValidationException($error, $message);
        }
    }
}
