<?php

declare(strict_types=1);

namespace App\DateTime;

use DateTime;
use DateTimeZone;

class DateTimeUTC
{
    public function createDateTimeInstance(string $time = 'now'): DateTime
    {
        return (new DateTime($time, new DateTimeZone('UTC')));
    }
}
