<?php

declare(strict_types=1);

namespace App\Validators;

use App\Checks\EmailCheck;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\ParameterBag;

class MalformedEmailInUrlParameter
{
    private EmailCheck $emailCheck;

    public function __construct(EmailCheck $emailCheck)
    {
        $this->emailCheck = $emailCheck;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfEmailUrlParameterIsMalformed(ParameterBag $parameters): void
    {
        if ($this->emailCheck->isEmailInValidFormat($parameters->get('email')) === false) {
            $data = Error::malformedEmail();
            $message = \sprintf(Emsg::MALFORMED_EMAIL);
            throw new ValidationException($data, $message);
        }
    }
}
