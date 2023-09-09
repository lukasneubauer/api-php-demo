<?php

declare(strict_types=1);

use App\Generators\TokenGenerator;

function generate_token(): string
{
    return (new TokenGenerator())->generateToken();
}
