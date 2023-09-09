<?php

declare(strict_types=1);

use App\Checks\EmailCheck;

function is_email_in_valid_format(string $email): bool
{
    return (new EmailCheck())->isEmailInValidFormat($email);
}
