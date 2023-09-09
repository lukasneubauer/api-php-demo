<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class UserIsTeacherSoPriceMustBeSet
{
    /**
     * @throws ValidationException
     */
    public function checkIfUserIsTeacherSoPriceIsSet(array $data): void
    {
        if ($data['isTeacher'] === true && $data['price'] === null) {
            $error = Error::userIsTeacherSoPriceMustBeSet();
            $message = \sprintf(Emsg::USER_IS_TEACHER_SO_PRICE_MUST_BE_SET);
            throw new ValidationException($error, $message);
        }
    }
}
