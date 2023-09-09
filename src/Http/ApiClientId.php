<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiClientIdFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiClientId
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiClientIdFoundException
     */
    public function getApiClientId(): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            throw new CouldNotGetCurrentRequestFromRequestStackException();
        }

        $apiClientId = $currentRequest->headers->get(ApiHeaders::API_CLIENT_ID);
        if (\is_string($apiClientId) === false) {
            throw new NoApiClientIdFoundException();
        }

        return $apiClientId;
    }
}
