<?php

declare(strict_types=1);

namespace Tests\App\Base64;

use App\Base64\Base64Encoder;
use PHPUnit\Framework\TestCase;

final class Base64EncoderTest extends TestCase
{
    public function testEncode(): void
    {
        $this->assertSame('TG9yZW0gaXBzdW0=', (new Base64Encoder())->encode('Lorem ipsum'));
    }
}
