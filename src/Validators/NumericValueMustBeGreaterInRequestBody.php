<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class NumericValueMustBeGreaterInRequestBody
{
    private string $property;

    private float $min;

    public function __construct(string $property, float $min)
    {
        $this->property = $property;
        $this->min = $min;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfPropertyNumericValueIsGreater(array $data): void
    {
        if ($data[$this->property] <= $this->min) {
            $error = Error::numericValueMustBeGreater($this->property, $this->min, $data[$this->property]);
            $message = \sprintf(Emsg::NUMERIC_VALUE_MUST_BE_GREATER, $this->property, $this->min, $data[$this->property]);
            throw new ValidationException($error, $message);
        }
    }
}
