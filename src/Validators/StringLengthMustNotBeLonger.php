<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class StringLengthMustNotBeLonger
{
    private string $property;

    private int $length;

    public function __construct(string $property, int $length)
    {
        $this->property = $property;
        $this->length = $length;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfStringLengthIsLonger(array $data): void
    {
        if (\strlen($data[$this->property]) > $this->length) {
            $error = Error::stringLengthMustNotBeLonger($this->property, $this->length);
            $message = \sprintf(Emsg::STRING_LENGTH_MUST_NOT_BE_LONGER, $this->property, $this->length);
            throw new ValidationException($error, $message);
        }
    }
}
