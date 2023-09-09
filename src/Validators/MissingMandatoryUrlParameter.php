<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;

class MissingMandatoryUrlParameter
{
    private string $expectedParameter;

    public function __construct(string $expectedParameter)
    {
        $this->expectedParameter = $expectedParameter;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUrlParameterIsMissing(ParameterBag $parameters): void
    {
        $parameter = $parameters->get($this->expectedParameter);
        if ($parameter === null) {
            $data = Error::missingMandatoryUrlParameter($this->expectedParameter);
            $message = \sprintf(Emsg::MISSING_MANDATORY_URL_PARAMETER, $this->expectedParameter);
            throw new ValidationException($data, $message);
        }
    }
}
