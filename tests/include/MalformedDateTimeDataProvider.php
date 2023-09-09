<?php

declare(strict_types=1);

namespace Tests;

final class MalformedDateTimeDataProvider
{
    public static function getMalformedDateTimes(): array
    {
        return [
            ['2000/12/24 20:30:00'],
            ['2000.12.24 20:30:00'],
            ['Sunday, 24-Dec-2000 20:30:00 UTC'], // Cookie
            ['Sun, 24 Dec 2000 20:30:00 +0000'], // RSS
            ['2000-12-24T20:30:00+00:00'], // W3C
        ];
    }
}
