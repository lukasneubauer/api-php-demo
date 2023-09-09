<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\SecurityCode;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\SecurityCodeHasToBeGeneratedAgainException;
use App\Repositories\UserRepository;

class SecurityCodeHasBeenGeneratedAgain
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws SecurityCodeHasToBeGeneratedAgainException
     */
    public function checkIfSecurityCodeHasToBeGeneratedAgain(array $data): void
    {
        $email = $data['email'];
        $user = $this->userRepository->getByEmail($email);
        $securityCode = $user->getSecurityCode();
        $givenSecurityCode = $data['securityCode'];
        $isGivenSecurityCodeCorrect = $givenSecurityCode === $securityCode->getCode();
        $lastAttempt = ($securityCode->getInputFailures() + 1) === SecurityCode::MAX_INPUT_FAILURE_ATTEMPTS;
        if ($isGivenSecurityCodeCorrect === false && $lastAttempt) {
            $error = Error::securityCodeHasBeenGeneratedAgain(SecurityCode::MAX_INPUT_FAILURE_ATTEMPTS);
            $message = \sprintf(Emsg::SECURITY_CODE_HAS_BEEN_GENERATED_AGAIN, SecurityCode::MAX_INPUT_FAILURE_ATTEMPTS);
            throw new SecurityCodeHasToBeGeneratedAgainException($error, $message);
        }
    }
}
