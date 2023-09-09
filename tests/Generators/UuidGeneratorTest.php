<?php

declare(strict_types=1);

namespace Tests\App\Generators;

use App\Checks\UuidCheck;
use App\Generators\UuidGenerator;
use PHPUnit\Framework\TestCase;

final class UuidGeneratorTest extends TestCase
{
    public function testGenerateUuid(): void
    {
        $uuidGenerator = new UuidGenerator();
        $uuid = $uuidGenerator->generateUuid();
        $uuidCheck = new UuidCheck();
        $this->assertTrue($uuidCheck->isUuidValid($uuid));
    }
}
