<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class NumberSizeMustNotBeHigher
{
    private string $property;

    private int $size;

    public function __construct(string $property, int $size)
    {
        $this->property = $property;
        $this->size = $size;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfNumberSizeIsHigher(array $data): void
    {
        if ($data[$this->property] > $this->size) {
            $error = Error::numberSizeMustNotBeHigher($this->property, $this->size);
            $message = \sprintf(Emsg::NUMBER_SIZE_MUST_NOT_BE_HIGHER, $this->property, $this->size);
            throw new ValidationException($error, $message);
        }
    }
}
