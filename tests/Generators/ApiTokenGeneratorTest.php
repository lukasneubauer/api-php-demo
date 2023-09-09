<?php

declare(strict_types=1);

namespace Tests\App\Generators;

use App\Generators\ApiTokenGenerator;
use PHPUnit\Framework\TestCase;

final class ApiTokenGeneratorTest extends TestCase
{
    public function testGenerateApiToken(): void
    {
        $apiTokenGenerator = new ApiTokenGenerator();
        $apiToken = $apiTokenGenerator->generateApiToken();
        $this->assertTrue(\strlen($apiToken) === 80);
    }
}
