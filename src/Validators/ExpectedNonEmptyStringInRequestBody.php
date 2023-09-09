<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class ExpectedNonEmptyStringInRequestBody
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfPropertyIsNonEmptyString(array $data): void
    {
        if (\strlen($data[$this->property]) === 0) {
            $error = Error::expectedDifferentValueInRequestBody($this->property, '""', 'empty string');
            $message = \sprintf(Emsg::EXPECTED_DIFFERENT_VALUE_IN_REQUEST_BODY, $this->property, '""', 'empty string');
            throw new ValidationException($error, $message);
        }
    }
}
