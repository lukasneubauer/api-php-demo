<?php

declare(strict_types=1);

namespace Tests\App\DateTime;

use App\DateTime\UTCToGivenTimezone;
use PHPUnit\Framework\TestCase;

final class UTCToGivenTimezoneTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testConvertUTCToGivenTimezone(string $timezone, string $time, string $expectedTime): void
    {
        $utcToGivenTimezone = new UTCToGivenTimezone();
        $convertedToGivenTimezone = $utcToGivenTimezone->convertUTCToGivenTimezone($timezone, $time);
        $this->assertSame($expectedTime, $convertedToGivenTimezone->format('Y-m-d H:i:s'));
        $this->assertSame($timezone, $convertedToGivenTimezone->getTimezone()->getName());
    }

    public static function getData(): array
    {
        return [
            [
                'America/Los_Angeles',
                '2000-01-01 12:00:00',
                '2000-01-01 04:00:00',
            ],
            [
                'America/New_York',
                '2000-01-01 12:00:00',
                '2000-01-01 07:00:00',
            ],
            [
                'Asia/Tokyo',
                '2000-01-01 12:00:00',
                '2000-01-01 21:00:00',
            ],
            [
                'Europe/Lisbon',
                '2000-01-01 12:00:00',
                '2000-01-01 12:00:00',
            ],
            [
                'Europe/Prague',
                '2000-01-01 12:00:00',
                '2000-01-01 13:00:00',
            ],
        ];
    }
}
