<?php

declare(strict_types=1);

namespace App\Checks;

use App\DateTime\DateTimeUTC;
use DateTime;

class ExpirationCheck
{
    private DateTimeUTC $dateTimeUTC;

    public function __construct(DateTimeUTC $dateTimeUTC)
    {
        $this->dateTimeUTC = $dateTimeUTC;
    }

    public function isExpired(?DateTime $timeOfCreation, int $expirationInSeconds): bool
    {
        $isExpired = true;

        if ($timeOfCreation !== null) {
            $nowTs = $this->dateTimeUTC->createDateTimeInstance()->getTimestamp();
            $timeOfCreationTs = $timeOfCreation->getTimestamp();
            $remainingTimeInSeconds = $nowTs - $timeOfCreationTs;
            $isExpired = $remainingTimeInSeconds > $expirationInSeconds || $remainingTimeInSeconds < 0;
        }

        return $isExpired;
    }
}
