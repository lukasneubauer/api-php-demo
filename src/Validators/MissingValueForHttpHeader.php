<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\HeaderBag;

class MissingValueForHttpHeader
{
    private string $expectedHeader;

    public function __construct(string $expectedHeader)
    {
        $this->expectedHeader = $expectedHeader;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfHttpHeaderIsEmpty(HeaderBag $headers): void
    {
        if (\strlen((string) $headers->get($this->expectedHeader)) === 0) {
            $data = Error::missingValueForHttpHeader($this->expectedHeader);
            $message = \sprintf(Emsg::MISSING_VALUE_FOR_HTTP_HEADER, $this->expectedHeader);
            throw new ValidationException($data, $message);
        }
    }
}
