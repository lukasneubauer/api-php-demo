<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\SecurityCode;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\SecurityCodeConfirmationFailureException;
use App\Repositories\UserRepository;

class IncorrectSecurityCodeHasBeenEntered
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws SecurityCodeConfirmationFailureException
     */
    public function checkIfIncorrectSecurityCodeHasBeenEntered(array $data): void
    {
        $user = $this->userRepository->getByEmail($data['email']);
        $securityCode = $user->getSecurityCode();
        $givenSecurityCode = $data['securityCode'];
        if ($givenSecurityCode !== $securityCode->getCode()) {
            $remainingAttempts = SecurityCode::MAX_INPUT_FAILURE_ATTEMPTS - $securityCode->getInputFailures() - 1;
            $error = Error::incorrectSecurityCodeHasBeenEntered($remainingAttempts);
            $message = \sprintf(Emsg::INCORRECT_SECURITY_CODE_HAS_BEEN_ENTERED, $remainingAttempts);
            throw new SecurityCodeConfirmationFailureException($error, $message);
        }
    }
}
