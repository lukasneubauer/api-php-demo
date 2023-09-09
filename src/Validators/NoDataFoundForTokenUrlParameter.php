<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class NoDataFoundForTokenUrlParameter
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForUrlParameterTokenWereFound(ParameterBag $parameters): void
    {
        if ($this->userRepository->getByToken($parameters->get('token')) === null) {
            $data = Error::noDataFoundForUrlParameter('token');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_URL_PARAMETER, 'token');
            throw new ValidationException($data, $message);
        }
    }
}
