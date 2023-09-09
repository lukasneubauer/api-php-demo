<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Password;

class PasswordFactory
{
    public function create(string $hash, string $algorithm): Password
    {
        return new Password($hash, $algorithm);
    }
}
