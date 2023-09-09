<?php

declare(strict_types=1);

namespace App\Passwords;

class PasswordCheck
{
    public function isPasswordCorrect(string $plainPassword, string $hash): bool
    {
        return \password_verify($plainPassword, $hash);
    }
}
