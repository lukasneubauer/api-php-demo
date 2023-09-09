<?php

declare(strict_types=1);

namespace Tests\App\DateTime;

use App\DateTime\DateTimeUTC;
use PHPUnit\Framework\TestCase;

final class DateTimeUTCTest extends TestCase
{
    public function testCreateDateTimeInstance(): void
    {
        $dateTimeString = '2000-01-01 12:00:00';
        $dateTimeUTC = new DateTimeUTC();
        $dateTime = $dateTimeUTC->createDateTimeInstance($dateTimeString);
        $this->assertSame($dateTimeString, $dateTime->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $dateTime->getTimezone()->getName());
    }
}
