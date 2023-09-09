<?php

declare(strict_types=1);

namespace App\Sessions;

use App\Entities\Session;
use App\Exceptions\SessionNotFoundByApiTokenException;
use App\Repositories\SessionRepository;

class SessionLocator
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws SessionNotFoundByApiTokenException
     */
    public function locateSession(string $apiToken): Session
    {
        $session = $this->sessionRepository->getByApiToken($apiToken);
        if ($session === null) {
            throw new SessionNotFoundByApiTokenException(
                \sprintf("Session not found by api token '%s'.", $apiToken)
            );
        }

        return $session;
    }
}
