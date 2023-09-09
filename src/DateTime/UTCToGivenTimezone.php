<?php

declare(strict_types=1);

namespace App\DateTime;

use DateTime;
use DateTimeZone;

class UTCToGivenTimezone
{
    public function convertUTCToGivenTimezone(string $timezone, string $time = 'now'): DateTime
    {
        return (new DateTime($time, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($timezone));
    }
}
