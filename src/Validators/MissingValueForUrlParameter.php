<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;

class MissingValueForUrlParameter
{
    private string $expectedParameter;

    public function __construct(string $expectedParameter)
    {
        $this->expectedParameter = $expectedParameter;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUrlParameterIsEmpty(ParameterBag $parameters): void
    {
        if (\strlen($parameters->get($this->expectedParameter)) === 0) {
            $data = Error::missingValueForUrlParameter($this->expectedParameter);
            $message = \sprintf(Emsg::MISSING_VALUE_FOR_URL_PARAMETER, $this->expectedParameter);
            throw new ValidationException($data, $message);
        }
    }
}
