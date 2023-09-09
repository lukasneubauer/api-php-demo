<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class NoDataFoundForCourseIdUrlParameter
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForUrlParameterCourseIdWereFound(ParameterBag $parameters): void
    {
        if ($this->courseRepository->getById($parameters->get('id')) === null) {
            $data = Error::noDataFoundForUrlParameter('id');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_URL_PARAMETER, 'id');
            throw new ValidationException($data, $message);
        }
    }
}
