<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class NoDataFoundForPropertyTeacherIdInRequestBody
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForPropertyTeacherIdWereFound(array $data): void
    {
        $user = $this->userRepository->getById($data['teacherId']);
        if ($user === null) {
            $error = Error::noDataFoundForPropertyInRequestBody('teacherId');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'teacherId');
            throw new ValidationException($error, $message);
        }
    }
}
