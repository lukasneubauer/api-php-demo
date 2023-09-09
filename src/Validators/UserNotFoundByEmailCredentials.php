<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class UserNotFoundByEmailCredentials
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUserEmailCredentialsAreCorrect(array $data): void
    {
        $email = $data['email'];
        $user = $this->userRepository->getByEmail($email);
        if ($user === null) {
            $error = Error::noDataFoundForPropertyInRequestBody('email');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'email');
            throw new ValidationException($error, $message);
        }
        if ($user->getToken() === null || $user->getToken()->getCode() !== $data['token']) {
            $error = Error::noDataFoundForPropertyInRequestBody('token');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'token');
            throw new ValidationException($error, $message);
        }
    }
}
