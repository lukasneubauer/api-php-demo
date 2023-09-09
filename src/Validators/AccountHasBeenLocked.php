<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\User;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\LockAccountException;
use App\Exceptions\ValidationException;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;

class AccountHasBeenLocked
{
    private UserRepository $userRepository;

    private PasswordCheck $passwordCheck;

    public function __construct(UserRepository $userRepository, PasswordCheck $passwordCheck)
    {
        $this->userRepository = $userRepository;
        $this->passwordCheck = $passwordCheck;
    }

    /**
     * @throws LockAccountException
     * @throws ValidationException
     */
    public function checkIfAccountHasBeenLocked(array $data): void
    {
        $email = $data['email'];
        $user = $this->userRepository->getByEmail($email);
        $password = $data['password'];
        $isPasswordCorrect = $this->passwordCheck->isPasswordCorrect($password, $user->getPassword()->getHash());
        $lastAttemptBeforeLock = ($user->getAuthenticationFailures() + 1) === User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS;
        if ($isPasswordCorrect === false && $lastAttemptBeforeLock) {
            $error = Error::accountHasBeenLocked(User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS);
            $message = \sprintf(Emsg::ACCOUNT_HAS_BEEN_LOCKED, User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS);
            throw new LockAccountException($error, $message);
        }
        if ($isPasswordCorrect === false && $user->isLocked()) {
            $error = Error::accountHasBeenLocked(User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS);
            $message = \sprintf(Emsg::ACCOUNT_HAS_BEEN_LOCKED, User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS);
            throw new ValidationException($error, $message);
        }
    }
}
