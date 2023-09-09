<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class MissingMandatoryPropertyInRequestBody
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfPropertyIsMissing(array $data): void
    {
        if (\array_key_exists($this->property, $data) === false) {
            $error = Error::missingMandatoryPropertyInRequestBody($this->property);
            $message = \sprintf(Emsg::MISSING_MANDATORY_PROPERTY_IN_REQUEST_BODY, $this->property);
            throw new ValidationException($error, $message);
        }
    }
}
