<?php

declare(strict_types=1);

namespace App\Sessions;

use App\DateTime\DateTimeUTC;
use App\Entities\Session;
use Exception;
use InvalidArgumentException;

class SessionLock
{
    private SessionLocator $sessionLocator;

    private DateTimeUTC $dateTimeUTC;

    public function __construct(SessionLocator $sessionLocator, DateTimeUTC $dateTimeUTC)
    {
        $this->sessionLocator = $sessionLocator;
        $this->dateTimeUTC = $dateTimeUTC;
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function lockSession(string $apiToken): Session
    {
        $now = $this->dateTimeUTC->createDateTimeInstance();

        $session = $this->sessionLocator->locateSession($apiToken);

        $session->setIsLocked(true);

        $session->setUpdatedAt($now);

        return $session;
    }
}
