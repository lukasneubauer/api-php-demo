<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class MissingJsonInRequestBody
{
    /**
     * @throws ValidationException
     */
    public function checkIfThereIsMissingJsonInRequestBody(string $requestBody): void
    {
        if (\strlen($requestBody) === 0) {
            $data = Error::missingJsonInRequestBody();
            $message = \sprintf(Emsg::MISSING_JSON_IN_REQUEST_BODY);
            throw new ValidationException($data, $message);
        }
    }
}
