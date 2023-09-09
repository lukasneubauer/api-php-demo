<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;

class NoDataFoundInCoursesForPropertyCourseIdInRequestBody
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataWereFoundInCoursesForPropertyCourseId(array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        if ($course === null) {
            $error = Error::noDataFoundForPropertyInRequestBody('courseId');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'courseId');
            throw new ValidationException($error, $message);
        }
    }
}
