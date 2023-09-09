<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;

class UserIsNotTeacherSoPriceMustNotBeSet
{
    /**
     * @throws ValidationException
     */
    public function checkIfUserIsNotTeacherSoPriceIsNotSet(array $data): void
    {
        if ($data['isTeacher'] === false && $data['price'] !== null) {
            $error = Error::userIsNotTeacherSoPriceMustNotBeSet();
            $message = \sprintf(Emsg::USER_IS_NOT_TEACHER_SO_PRICE_MUST_NOT_BE_SET);
            throw new ValidationException($error, $message);
        }
    }
}
