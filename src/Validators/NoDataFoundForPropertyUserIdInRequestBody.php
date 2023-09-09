<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class NoDataFoundForPropertyUserIdInRequestBody
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForPropertyUserIdWereFound(array $data): void
    {
        $user = $this->userRepository->getById($data['id']);
        if ($user === null) {
            $error = Error::noDataFoundForPropertyInRequestBody('id');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'id');
            throw new ValidationException($error, $message);
        }
    }
}
