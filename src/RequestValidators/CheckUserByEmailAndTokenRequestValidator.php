<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use App\Exceptions\TokenExpiredException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class CheckUserByEmailAndTokenRequestValidator
{
    private RequestValidator $requestValidator;

    public function __construct(RequestValidator $requestValidator)
    {
        $this->requestValidator = $requestValidator;
    }

    /**
     * @throws RequestValidationException
     * @throws TokenExpiredException
     */
    public function validateRequest(
        HeaderBag $headers,
        string $method,
        ParameterBag $parameters
    ): void {
        if ($this->requestValidator->performRequestValidation() === false) {
            return;
        }

        try {
            $this->requestValidator->checkIfApiKeyHttpHeaderIsMissing($headers);
            $this->requestValidator->checkIfApiKeyHttpHeaderIsEmpty($headers);
            $this->requestValidator->checkIfApiKeyHttpHeaderIsInvalid($headers);
            $this->requestValidator->checkIfHttpMethodIsGet($method);
            $this->requestValidator->checkIfEmailUrlParameterIsMissing($parameters);
            $this->requestValidator->checkIfEmailUrlParameterIsEmpty($parameters);
            $this->requestValidator->checkIfEmailUrlParameterIsMalformed($parameters);
            $this->requestValidator->checkIfTokenUrlParameterIsMissing($parameters);
            $this->requestValidator->checkIfTokenUrlParameterIsEmpty($parameters);
            $this->requestValidator->checkIfUserEmailCredentialsInUrlParametersAreCorrect($parameters);
            $this->requestValidator->checkIfTokenInUrlParameterHasExpired($parameters);
        } catch (RequestValidationException | TokenExpiredException $e) {
            throw $e;
        }
    }
}
