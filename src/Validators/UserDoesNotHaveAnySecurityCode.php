<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class UserDoesNotHaveAnySecurityCode
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUserDoesHaveAnySecurityCode(array $data): void
    {
        $email = $data['email'];
        $user = $this->userRepository->getByEmail($email);
        if ($user->getSecurityCode() === null) {
            $error = Error::userDoesNotHaveAnySecurityCode();
            $message = \sprintf(Emsg::USER_DOES_NOT_HAVE_ANY_SECURITY_CODE);
            throw new ValidationException($error, $message);
        }
    }
}
