<?php

declare(strict_types=1);

namespace Tests\App\Checks;

use App\Checks\UuidCheck;
use App\Generators\UuidGenerator;
use PHPUnit\Framework\TestCase;

final class UuidCheckTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testIsUuidValid(string $uuid, bool $expectedUuidValidity): void
    {
        $uuidCheck = new UuidCheck();
        $this->assertSame($expectedUuidValidity, $uuidCheck->isUuidValid($uuid));
    }

    public static function getData(): array
    {
        return [
            [
                '1',
                false,
            ],
            [
                '00000000-0000-0000-0000-000000000000',
                false,
            ],
            [
                '00000000-0000-1000-z000-000000000000',
                false,
            ],
            [
                '00000000-0000-a000-0000-000000000000',
                false,
            ],
            [
                'c9358499-5952-9558-9a37-a43b09b8c74e',
                false,
            ],
            [
                (new UuidGenerator())->generateUuid(),
                true,
            ],
        ];
    }
}
