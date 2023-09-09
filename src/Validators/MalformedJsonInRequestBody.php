<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class MalformedJsonInRequestBody
{
    /**
     * @throws ValidationException
     */
    public function checkIfThereIsMalformedJsonInRequestBody(?array $data): void
    {
        if (\is_array($data) === false) {
            $error = Error::malformedJsonInRequestBody();
            $message = \sprintf(Emsg::MALFORMED_JSON_IN_REQUEST_BODY);
            throw new ValidationException($error, $message);
        }
    }
}
