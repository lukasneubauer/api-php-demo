<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\LessonRepository;

class NoDataFoundForPropertyLessonIdInRequestBody
{
    private LessonRepository $lessonRepository;

    public function __construct(LessonRepository $lessonRepository)
    {
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForPropertyLessonIdWereFound(array $data): void
    {
        $lesson = $this->lessonRepository->getById($data['id']);
        if ($lesson === null) {
            $error = Error::noDataFoundForPropertyInRequestBody('id');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'id');
            throw new ValidationException($error, $message);
        }
    }
}
