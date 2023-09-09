<?php

declare(strict_types=1);

use App\Generators\SecurityCodeGenerator;

function generate_security_code(): string
{
    return (new SecurityCodeGenerator())->generateSecurityCode();
}
