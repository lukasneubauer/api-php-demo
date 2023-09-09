<?php

declare(strict_types=1);

use App\DateTime\DateTimeUTCFromTimestamp;

function date_time_utc_from_timestamp(int $timestamp): DateTime
{
    return (new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($timestamp);
}
