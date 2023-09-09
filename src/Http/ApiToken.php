<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiToken
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     */
    public function getApiToken(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            throw new CouldNotGetCurrentRequestFromRequestStackException();
        }

        $apiToken = $currentRequest->headers->get(ApiHeaders::API_TOKEN);
        if (\is_string($apiToken) === false) {
            throw new NoApiTokenFoundException();
        }

        return $apiToken;
    }
}
