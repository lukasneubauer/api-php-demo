<?php

declare(strict_types=1);

namespace Tests\App\Generators;

use App\Generators\TokenGenerator;
use PHPUnit\Framework\TestCase;

final class TokenGeneratorTest extends TestCase
{
    public function testGenerateToken(): void
    {
        $tokenGenerator = new TokenGenerator();
        $token = $tokenGenerator->generateToken();
        $this->assertTrue(\strlen($token) === 20);
    }
}
