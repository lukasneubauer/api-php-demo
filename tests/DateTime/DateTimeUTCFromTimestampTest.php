<?php

declare(strict_types=1);

namespace Tests\App\DateTime;

use App\DateTime\DateTimeUTCFromTimestamp;
use PHPUnit\Framework\TestCase;

final class DateTimeUTCFromTimestampTest extends TestCase
{
    public function testCreateDateTimeInstanceFromTimestamp(): void
    {
        $timestamp = 0;
        $dateTimeFromTimestamp = new DateTimeUTCFromTimestamp();
        $dateTime = $dateTimeFromTimestamp->createDateTimeInstanceFromTimestamp($timestamp);
        $this->assertSame($timestamp, $dateTime->getTimestamp());
        $this->assertSame('UTC', $dateTime->getTimezone()->getName());
    }
}
