<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class SelectedTimezoneIsInvalid
{
    /**
     * @throws ValidationException
     */
    public function checkIfSelectedTimezoneIsInvalid(array $data): void
    {
        if (\in_array($data['timezone'], \timezone_identifiers_list(), true) === false) {
            $error = Error::selectedTimezoneIsInvalid($data['timezone']);
            $message = \sprintf(Emsg::SELECTED_TIMEZONE_IS_INVALID, $data['timezone']);
            throw new ValidationException($error, $message);
        }
    }
}
