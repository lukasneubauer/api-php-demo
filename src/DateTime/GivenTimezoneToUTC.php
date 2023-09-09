<?php

declare(strict_types=1);

namespace App\DateTime;

use DateTime;
use DateTimeZone;

class GivenTimezoneToUTC
{
    public function convertGivenTimezoneToUTC(string $timezone, string $time = 'now'): DateTime
    {
        return (new DateTime($time, new DateTimeZone($timezone)))->setTimezone(new DateTimeZone('UTC'));
    }
}
