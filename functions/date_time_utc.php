<?php

declare(strict_types=1);

use App\DateTime\DateTimeUTC;

function date_time_utc(string $time = 'now'): DateTime
{
    return (new DateTimeUTC())->createDateTimeInstance($time);
}
