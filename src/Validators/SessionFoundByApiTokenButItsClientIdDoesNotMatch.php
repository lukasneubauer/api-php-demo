<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Http\ApiHeaders;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class SessionFoundByApiTokenButItsClientIdDoesNotMatch
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws SessionHasNotMatchingClientIdException
     */
    public function checkIfSessionFoundByApiTokenButItsClientIdDoesNotMatch(HeaderBag $headers): void
    {
        $apiClientId = (string) $headers->get(ApiHeaders::API_CLIENT_ID);
        $apiToken = (string) $headers->get(ApiHeaders::API_TOKEN);
        $session = $this->sessionRepository->getByApiToken($apiToken);
        if ($session->getApiClientId() !== $apiClientId) {
            $data = Error::sessionFoundByApiTokenButItsClientIdDoesNotMatch();
            $message = \sprintf(Emsg::SESSION_FOUND_BY_API_TOKEN_BUT_ITS_CLIENT_ID_DOES_NOT_MATCH);
            throw new SessionHasNotMatchingClientIdException($data, $message);
        }
    }
}
