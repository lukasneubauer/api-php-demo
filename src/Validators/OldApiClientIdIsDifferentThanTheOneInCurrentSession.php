<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class OldApiClientIdIsDifferentThanTheOneInCurrentSession
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfOldApiClientIdIsDifferentThanTheOneInCurrentSession(HeaderBag $headers, array $data): void
    {
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));
        if ($session->getApiClientId() !== $data['oldApiClientId']) {
            $error = Error::oldApiClientIdIsDifferentThanTheOneInCurrentSession();
            $message = \sprintf(Emsg::OLD_API_CLIENT_ID_IS_DIFFERENT_THAN_THE_ONE_IN_CURRENT_SESSION);
            throw new ValidationException($error, $message);
        }
    }
}
