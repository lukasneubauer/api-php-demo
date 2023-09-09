<?php

declare(strict_types=1);

namespace Tests\App\Json;

use App\Json\JsonEncoder;
use PHPUnit\Framework\TestCase;

final class JsonEncoderTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testEncode(array $data, string $expectedResult): void
    {
        $jsonEncoder = new JsonEncoder();
        $result = $jsonEncoder->encode($data);
        $this->assertSame($expectedResult, $result);
    }

    public static function getData(): array
    {
        return [
            [
                [],
                '[]',
            ],
            [
                [
                    'a' => 1,
                    'b' => 2,
                ],
                '{"a":1,"b":2}',
            ],
            [
                [
                    'a' => 1,
                    'b' => true,
                    'c' => [
                        1,
                        2,
                        null,
                    ],
                ],
                '{"a":1,"b":true,"c":[1,2,null]}',
            ],
        ];
    }
}
