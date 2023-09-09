<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use Symfony\Component\HttpFoundation\HeaderBag;

class LogoutRequestValidator
{
    private RequestValidator $requestValidator;

    public function __construct(RequestValidator $requestValidator)
    {
        $this->requestValidator = $requestValidator;
    }

    /**
     * @throws RequestValidationException
     * @throws SessionHasNotMatchingClientIdException
     */
    public function validateRequest(
        HeaderBag $headers,
        string $method
    ): void {
        if ($this->requestValidator->performRequestValidation() === false) {
            return;
        }

        try {
            $this->requestValidator->checkIfApiKeyHttpHeaderIsMissing($headers);
            $this->requestValidator->checkIfApiKeyHttpHeaderIsEmpty($headers);
            $this->requestValidator->checkIfApiKeyHttpHeaderIsInvalid($headers);
            $this->requestValidator->checkIfApiClientIdHttpHeaderIsMissing($headers);
            $this->requestValidator->checkIfApiClientIdHttpHeaderIsEmpty($headers);
            $this->requestValidator->checkIfApiClientIdHttpHeaderIsInvalid($headers);
            $this->requestValidator->checkIfApiTokenHttpHeaderIsMissing($headers);
            $this->requestValidator->checkIfApiTokenHttpHeaderIsEmpty($headers);
            $this->requestValidator->checkIfApiTokenHttpHeaderIsInvalid($headers);
            $this->requestValidator->checkIfSessionFoundByApiTokenButItsClientIdDoesNotMatch($headers);
            $this->requestValidator->checkIfSessionIsLocked($headers);
            $this->requestValidator->checkIfHttpMethodIsPost($method);
        } catch (RequestValidationException $e) {
            throw $e;
        }
    }
}
