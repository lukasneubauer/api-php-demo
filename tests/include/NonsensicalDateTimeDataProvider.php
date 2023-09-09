<?php

declare(strict_types=1);

namespace Tests;

final class NonsensicalDateTimeDataProvider
{
    public static function getNonsensicalDateTimes(): array
    {
        return [
            ['2000-99-24 20:30:00'],
            ['2000-12-99 20:30:00'],
            ['2000-12-24 99:30:00'],
            ['2000-12-24 20:99:00'],
            ['2000-12-24 20:30:99'],
            ['2000-99-99 99:99:99'],
        ];
    }
}
