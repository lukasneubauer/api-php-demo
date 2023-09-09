<?php

declare(strict_types=1);

namespace App\Checks;

class EmailCheck
{
    public function isEmailInValidFormat(string $email): bool
    {
        return \preg_match('#^.+\@\S+\.\S+$#', $email) === 1;
    }
}
