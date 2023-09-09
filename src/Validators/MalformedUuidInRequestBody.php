<?php

declare(strict_types=1);

namespace App\Validators;

use App\Checks\UuidCheck;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class MalformedUuidInRequestBody
{
    private UuidCheck $uuidCheck;

    private string $property;

    public function __construct(UuidCheck $uuidCheck, string $property)
    {
        $this->uuidCheck = $uuidCheck;
        $this->property = $property;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUuidIsMalformed(array $data): void
    {
        if ($this->uuidCheck->isUuidValid($data[$this->property]) === false) {
            $error = Error::malformedUuid();
            $message = \sprintf(Emsg::MALFORMED_UUID);
            throw new ValidationException($error, $message);
        }
    }
}
