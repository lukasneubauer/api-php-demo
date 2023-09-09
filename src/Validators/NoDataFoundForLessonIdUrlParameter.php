<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\LessonRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class NoDataFoundForLessonIdUrlParameter
{
    private LessonRepository $lessonRepository;

    public function __construct(LessonRepository $lessonRepository)
    {
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForUrlParameterLessonIdWereFound(ParameterBag $parameters): void
    {
        if ($this->lessonRepository->getById($parameters->get('id')) === null) {
            $data = Error::noDataFoundForUrlParameter('id');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_URL_PARAMETER, 'id');
            throw new ValidationException($data, $message);
        }
    }
}
