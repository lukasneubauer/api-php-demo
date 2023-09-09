<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class MalformedDateTimeInRequestBody
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfDateTimeIsMalformed(array $data): void
    {
        if (\preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$#', $data[$this->property]) !== 1) {
            $error = Error::malformedDateTime($this->property);
            $message = \sprintf(Emsg::MALFORMED_DATETIME, $this->property);
            throw new ValidationException($error, $message);
        }
    }
}
