<?php

declare(strict_types=1);

namespace Tests\App\Entities;

use App\DateTime\DateTimeUTC;
use App\Entities\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    /**
     * @dataProvider getParams
     */
    public function testIsExpired(string $relativeDate, bool $isExpired): void
    {
        $token = new Token('token', (new DateTimeUTC())->createDateTimeInstance($relativeDate));
        $this->assertSame($isExpired, $token->isExpired());
    }

    public static function getParams(): array
    {
        return [
            [
                '-90 min',
                true,
            ],
            [
                '-61 min',
                true,
            ],
            [
                '-60 min -1 sec',
                true,
            ],
            [
                '-60 min',
                false,
            ],
            [
                '-59 min -59 sec',
                false,
            ],
            [
                '-59 min',
                false,
            ],
            [
                '-30 min',
                false,
            ],
            [
                '-10 min',
                false,
            ],
            [
                '-1 min',
                false,
            ],
            [
                '0 min -1 sec',
                false,
            ],
            [
                '0 min',
                false,
            ],
            [
                '0 min +1 sec',
                true,
            ],
            [
                '+1 min',
                true,
            ],
            [
                '+10 min',
                true,
            ],
            [
                '+30 min',
                true,
            ],
            [
                '+60 min',
                true,
            ],
            [
                '+90 min',
                true,
            ],
        ];
    }
}
