<?php

declare(strict_types=1);

namespace Tests\App\Json;

use App\Json\JsonDecoder;
use PHPUnit\Framework\TestCase;

final class JsonDecoderTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testDecode(string $json, ?array $expectedResult): void
    {
        $jsonDecoder = new JsonDecoder();
        $result = $jsonDecoder->decode($json);
        $this->assertSame($expectedResult, $result);
    }

    public static function getData(): array
    {
        return [
            [
                '',
                null,
            ],
            [
                '{',
                null,
            ],
            [
                '{}',
                [],
            ],
            [
                '[]',
                [],
            ],
            [
                '{a:1,b:2}',
                null,
            ],
            [
                '{"a":1,"b":2}',
                [
                    'a' => 1,
                    'b' => 2,
                ],
            ],
            [
                '{"a":1,"b":true,"c":[1,2,null]}',
                [
                    'a' => 1,
                    'b' => true,
                    'c' => [
                        1,
                        2,
                        null,
                    ],
                ],
            ],
        ];
    }
}
