<?php

declare(strict_types=1);

namespace App\Generators;

use App\Entities\SecurityCode;
use Nette\Utils\Random;

class SecurityCodeGenerator
{
    public function generateSecurityCode(): string
    {
        return Random::generate(SecurityCode::LENGTH, SecurityCode::PATTERN);
    }
}
