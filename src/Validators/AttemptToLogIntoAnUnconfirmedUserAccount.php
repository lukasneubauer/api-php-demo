<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class AttemptToLogIntoAnUnconfirmedUserAccount
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUserIsAttemptingToLogIntoAnUnconfirmedAccount(array $data): void
    {
        $email = $data['email'];
        $user = $this->userRepository->getByEmail($email);
        if ($user->isActive() === false) {
            $error = Error::attemptToLogIntoAnUnconfirmedUserAccount();
            $message = \sprintf(Emsg::ATTEMPT_TO_LOG_INTO_AN_UNCONFIRMED_USER_ACCOUNT);
            throw new ValidationException($error, $message);
        }
    }
}
