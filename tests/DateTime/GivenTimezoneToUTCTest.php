<?php

declare(strict_types=1);

namespace Tests\App\DateTime;

use App\DateTime\GivenTimezoneToUTC;
use PHPUnit\Framework\TestCase;

final class GivenTimezoneToUTCTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testConvertGivenTimezoneToUTC(string $timezone, string $time, string $expectedTime): void
    {
        $givenTimezoneToUTC = new GivenTimezoneToUTC();
        $convertedToUTC = $givenTimezoneToUTC->convertGivenTimezoneToUTC($timezone, $time);
        $this->assertSame($expectedTime, $convertedToUTC->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $convertedToUTC->getTimezone()->getName());
    }

    public static function getData(): array
    {
        return [
            [
                'America/Los_Angeles',
                '2000-01-01 12:00:00',
                '2000-01-01 20:00:00',
            ],
            [
                'America/New_York',
                '2000-01-01 12:00:00',
                '2000-01-01 17:00:00',
            ],
            [
                'Asia/Tokyo',
                '2000-01-01 12:00:00',
                '2000-01-01 03:00:00',
            ],
            [
                'Europe/Lisbon',
                '2000-01-01 12:00:00',
                '2000-01-01 12:00:00',
            ],
            [
                'Europe/Prague',
                '2000-01-01 12:00:00',
                '2000-01-01 11:00:00',
            ],
        ];
    }
}
