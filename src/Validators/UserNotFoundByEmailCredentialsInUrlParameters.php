<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserNotFoundByEmailCredentialsInUrlParameters
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUserEmailCredentialsInUrlParametersAreCorrect(ParameterBag $parameters): void
    {
        $email = $parameters->get('email');
        $user = $this->userRepository->getByEmail($email);
        if ($user === null) {
            $data = Error::noDataFoundForUrlParameter('email');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_URL_PARAMETER, 'email');
            throw new ValidationException($data, $message);
        }
        if ($user->getToken() === null || $user->getToken()->getCode() !== $parameters->get('token')) {
            $data = Error::noDataFoundForUrlParameter('token');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_URL_PARAMETER, 'token');
            throw new ValidationException($data, $message);
        }
    }
}
