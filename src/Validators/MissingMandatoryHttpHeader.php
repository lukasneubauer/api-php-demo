<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\HeaderBag;

class MissingMandatoryHttpHeader
{
    private string $expectedHeader;

    public function __construct(string $expectedHeader)
    {
        $this->expectedHeader = $expectedHeader;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfHttpHeaderIsMissing(HeaderBag $headers): void
    {
        if ($headers->get($this->expectedHeader) === null) {
            $data = Error::missingMandatoryHttpHeader($this->expectedHeader);
            $message = \sprintf(Emsg::MISSING_MANDATORY_HTTP_HEADER, $this->expectedHeader);
            throw new ValidationException($data, $message);
        }
    }
}
