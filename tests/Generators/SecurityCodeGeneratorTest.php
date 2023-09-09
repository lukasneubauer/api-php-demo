<?php

declare(strict_types=1);

namespace Tests\App\Generators;

use App\Generators\SecurityCodeGenerator;
use PHPUnit\Framework\TestCase;

final class SecurityCodeGeneratorTest extends TestCase
{
    public function testGenerateSecurityCode(): void
    {
        $securityCodeGenerator = new SecurityCodeGenerator();
        $securityCode = $securityCodeGenerator->generateSecurityCode();
        $this->assertTrue(\strlen($securityCode) === 9);
    }
}
