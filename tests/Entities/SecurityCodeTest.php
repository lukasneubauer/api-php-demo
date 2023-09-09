<?php

declare(strict_types=1);

namespace Tests\App\Entities;

use App\DateTime\DateTimeUTC;
use App\Entities\SecurityCode;
use PHPUnit\Framework\TestCase;

class SecurityCodeTest extends TestCase
{
    /**
     * @dataProvider getParams
     */
    public function testIsExpired(string $relativeDate, bool $isExpired): void
    {
        $securityCode = new SecurityCode('security code', (new DateTimeUTC())->createDateTimeInstance($relativeDate));
        $this->assertSame($isExpired, $securityCode->isExpired());
    }

    public static function getParams(): array
    {
        return [
            [
                '-7 min',
                true,
            ],
            [
                '-6 min',
                true,
            ],
            [
                '-5 min -1 sec',
                true,
            ],
            [
                '-5 min',
                false,
            ],
            [
                '-4 min -59 sec',
                false,
            ],
            [
                '-4 min',
                false,
            ],
            [
                '-3 min',
                false,
            ],
            [
                '-2 min',
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
                '+2 min',
                true,
            ],
            [
                '+3 min',
                true,
            ],
            [
                '+4 min',
                true,
            ],
            [
                '+5 min',
                true,
            ],
        ];
    }
}
