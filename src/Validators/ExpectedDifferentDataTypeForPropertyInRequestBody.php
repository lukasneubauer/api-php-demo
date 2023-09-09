<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class ExpectedDifferentDataTypeForPropertyInRequestBody
{
    private string $expectedDataType;

    private string $property;

    public function __construct(string $expectedDataType, string $property)
    {
        $this->expectedDataType = $expectedDataType;
        $this->property = $property;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfPropertyIsOfCorrectDataType(array $data): void
    {
        $givenDataType = \strtolower(\gettype($data[$this->property]));
        if ($givenDataType !== $this->expectedDataType) {
            $error = Error::expectedDifferentDataTypeInRequestBody($this->expectedDataType, $this->property, $givenDataType);
            $message = \sprintf(Emsg::EXPECTED_DIFFERENT_DATA_TYPE_IN_REQUEST_BODY, $this->expectedDataType, $this->property, $givenDataType);
            throw new ValidationException($error, $message);
        }
    }
}
