<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class SessionIsLocked
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfSessionIsLocked(HeaderBag $headers): void
    {
        $apiToken = (string) $headers->get(ApiHeaders::API_TOKEN);
        $session = $this->sessionRepository->getByApiToken($apiToken);
        if ($session->isLocked()) {
            $data = Error::sessionIsLocked();
            $message = \sprintf(Emsg::SESSION_IS_LOCKED);
            throw new ValidationException($data, $message);
        }
    }
}
