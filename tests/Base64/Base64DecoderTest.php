<?php

declare(strict_types=1);

namespace Tests\App\Base64;

use App\Base64\Base64Decoder;
use PHPUnit\Framework\TestCase;

final class Base64DecoderTest extends TestCase
{
    public function testDecode(): void
    {
        $this->assertSame('Lorem ipsum', (new Base64Decoder())->decode('TG9yZW0gaXBzdW0='));
    }
}
