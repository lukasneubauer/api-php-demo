<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\SecurityCode;
use App\Generators\SecurityCodeGenerator;
use DateTime;

class SecurityCodeFactory
{
    private SecurityCodeGenerator $securityCodeGenerator;

    public function __construct(SecurityCodeGenerator $securityCodeGenerator)
    {
        $this->securityCodeGenerator = $securityCodeGenerator;
    }

    public function create(DateTime $now): SecurityCode
    {
        return new SecurityCode(
            $this->securityCodeGenerator->generateSecurityCode(),
            $now
        );
    }
}
