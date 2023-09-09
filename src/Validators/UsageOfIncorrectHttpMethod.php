<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class UsageOfIncorrectHttpMethod
{
    private string $expectedMethod;

    public function __construct(string $expectedMethod)
    {
        $this->expectedMethod = $expectedMethod;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfHttpMethodIsCorrect(string $givenMethod): void
    {
        if ($this->expectedMethod !== $givenMethod) {
            $data = Error::usageOfIncorrectHttpMethod($givenMethod, $this->expectedMethod);
            $message = \sprintf(Emsg::USAGE_OF_INCORRECT_HTTP_METHOD, $givenMethod, $this->expectedMethod);
            throw new ValidationException($data, $message);
        }
    }
}
