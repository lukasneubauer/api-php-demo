<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class InvalidValueForApiTokenHttpHeader
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfApiTokenIsInvalid(HeaderBag $headers): void
    {
        if ($this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN)) === null) {
            $data = Error::invalidValueForHttpHeader(ApiHeaders::API_TOKEN);
            $message = \sprintf(Emsg::INVALID_VALUE_FOR_HTTP_HEADER, ApiHeaders::API_TOKEN);
            throw new ValidationException($data, $message);
        }
    }
}
