<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;

class UserNotFoundByCredentials
{
    private UserRepository $userRepository;

    private PasswordCheck $passwordCheck;

    public function __construct(UserRepository $userRepository, PasswordCheck $passwordCheck)
    {
        $this->userRepository = $userRepository;
        $this->passwordCheck = $passwordCheck;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUserCredentialsAreCorrect(array $data): void
    {
        $email = $data['email'];
        $user = $this->userRepository->getByEmail($email);
        if ($user === null) {
            $error = Error::noDataFoundForPropertyInRequestBody('email');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'email');
            throw new ValidationException($error, $message);
        }
        $password = $data['password'];
        if ($this->passwordCheck->isPasswordCorrect($password, $user->getPassword()->getHash()) === false) {
            $error = Error::noDataFoundForPropertyInRequestBody('password');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, 'password');
            throw new ValidationException($error, $message);
        }
    }
}
