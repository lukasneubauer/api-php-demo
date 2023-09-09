<?php

declare(strict_types=1);

namespace App\DateTime;

use DateTime;
use DateTimeZone;

class DateTimeUTCFromTimestamp
{
    public function createDateTimeInstanceFromTimestamp(int $timestamp): DateTime
    {
        return (new DateTime('now', new DateTimeZone('UTC')))->setTimestamp($timestamp);
    }
}
