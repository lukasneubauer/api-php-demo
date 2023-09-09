<?php

declare(strict_types=1);

namespace App\Validators;

use App\Checks\EmailCheck;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class MalformedEmailInRequestBody
{
    private EmailCheck $emailCheck;

    public function __construct(EmailCheck $emailCheck)
    {
        $this->emailCheck = $emailCheck;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfPropertyEmailIsMalformed(array $data): void
    {
        if ($this->emailCheck->isEmailInValidFormat($data['email']) === false) {
            $error = Error::malformedEmail();
            $message = \sprintf(Emsg::MALFORMED_EMAIL);
            throw new ValidationException($error, $message);
        }
    }
}
