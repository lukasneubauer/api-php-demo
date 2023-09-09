<?php

declare(strict_types=1);

namespace Tests;

use App\Generators\TokenGenerator;

final class TokenGeneratorWithPredefinedToken extends TokenGenerator
{
    public function generateToken(): string
    {
        return 'xvizlczgexbhr404ud0k';
    }
}
