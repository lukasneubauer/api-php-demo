<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class NoDataFoundForEmailUrlParameter
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfAnyDataForUrlParameterEmailWereFound(ParameterBag $parameters): void
    {
        if ($this->userRepository->getByEmail($parameters->get('email')) === null) {
            $data = Error::noDataFoundForUrlParameter('email');
            $message = \sprintf(Emsg::NO_DATA_FOUND_FOR_URL_PARAMETER, 'email');
            throw new ValidationException($data, $message);
        }
    }
}
