<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class ExpectedNumberForPropertyPriceInRequestBody
{
    /**
     * @throws ValidationException
     */
    public function checkIfPropertyPriceIsNumber(array $data): void
    {
        $property = $data['price'];
        $givenDataType = \strtolower(\gettype($property));
        if (\is_int($property) === false) {
            $error = Error::expectedDifferentDataTypeInRequestBody('integer', 'price', $givenDataType);
            $message = \sprintf(Emsg::EXPECTED_DIFFERENT_DATA_TYPE_IN_REQUEST_BODY, 'integer', 'price', $givenDataType);
            throw new ValidationException($error, $message);
        }
    }
}
