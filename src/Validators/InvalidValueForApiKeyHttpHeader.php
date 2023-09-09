<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Http\ApiKey;
use Symfony\Component\HttpFoundation\HeaderBag;

class InvalidValueForApiKeyHttpHeader
{
    private ApiKey $apiKey;

    public function __construct(ApiKey $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfApiKeyIsInvalid(HeaderBag $headers): void
    {
        $apiKey = (string) $headers->get(ApiHeaders::API_KEY);
        $expectedApiKey = $this->apiKey->getApiKey();
        if ($apiKey !== $expectedApiKey) {
            $data = Error::invalidValueForHttpHeader(ApiHeaders::API_KEY);
            $message = \sprintf(Emsg::INVALID_VALUE_FOR_HTTP_HEADER, ApiHeaders::API_KEY);
            throw new ValidationException($data, $message);
        }
    }
}
