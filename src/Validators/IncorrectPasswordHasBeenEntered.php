<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\User;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\AuthenticationFailureException;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;

class IncorrectPasswordHasBeenEntered
{
    private UserRepository $userRepository;

    private PasswordCheck $passwordCheck;

    public function __construct(UserRepository $userRepository, PasswordCheck $passwordCheck)
    {
        $this->userRepository = $userRepository;
        $this->passwordCheck = $passwordCheck;
    }

    /**
     * @throws AuthenticationFailureException
     */
    public function checkIfIncorrectPasswordHasBeenEntered(array $data): void
    {
        $user = $this->userRepository->getByEmail($data['email']);
        $password = $data['password'];
        if ($this->passwordCheck->isPasswordCorrect($password, $user->getPassword()->getHash()) === false) {
            $remainingAttempts = User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS - $user->getAuthenticationFailures() - 1;
            $error = Error::incorrectPasswordHasBeenEntered($remainingAttempts);
            $message = \sprintf(Emsg::INCORRECT_PASSWORD_HAS_BEEN_ENTERED, $remainingAttempts);
            throw new AuthenticationFailureException($error, $message);
        }
    }
}
