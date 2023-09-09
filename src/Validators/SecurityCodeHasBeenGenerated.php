<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\User;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\SecurityCodeHasToBeGeneratedException;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;

class SecurityCodeHasBeenGenerated
{
    private UserRepository $userRepository;

    private PasswordCheck $passwordCheck;

    public function __construct(UserRepository $userRepository, PasswordCheck $passwordCheck)
    {
        $this->userRepository = $userRepository;
        $this->passwordCheck = $passwordCheck;
    }

    /**
     * @throws SecurityCodeHasToBeGeneratedException
     */
    public function checkIfSecurityCodeHasToBeGenerated(array $data): void
    {
        $email = $data['email'];
        $user = $this->userRepository->getByEmail($email);
        $password = $data['password'];
        $isPasswordCorrect = $this->passwordCheck->isPasswordCorrect($password, $user->getPassword()->getHash());
        if ($isPasswordCorrect && $user->isLocked()) {
            $error = Error::securityCodeHasBeenGenerated(User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS);
            $message = \sprintf(Emsg::SECURITY_CODE_HAS_BEEN_GENERATED, User::MAX_AUTHENTICATION_FAILURE_ATTEMPTS);
            throw new SecurityCodeHasToBeGeneratedException($error, $message);
        }
    }
}
