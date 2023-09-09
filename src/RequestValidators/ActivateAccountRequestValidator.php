<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use App\Exceptions\TokenExpiredException;
use Symfony\Component\HttpFoundation\HeaderBag;

class ActivateAccountRequestValidator
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
        string $requestBody,
        ?array $data
    ): void {
        if ($this->requestValidator->performRequestValidation() === false) {
            return;
        }

        try {
            $this->requestValidator->checkIfApiKeyHttpHeaderIsMissing($headers);
            $this->requestValidator->checkIfApiKeyHttpHeaderIsEmpty($headers);
            $this->requestValidator->checkIfApiKeyHttpHeaderIsInvalid($headers);
            $this->requestValidator->checkIfHttpMethodIsPost($method);
            $this->requestValidator->checkIfThereIsMissingJsonInRequestBody($requestBody);
            $this->requestValidator->checkIfThereIsMalformedJsonInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsMalformedInRequestBody($data);
            $this->requestValidator->checkIfPropertyTokenIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyTokenIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyTokenIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfUserEmailCredentialsAreCorrect($data);
            $this->requestValidator->checkIfTokenHasExpired($data);
        } catch (RequestValidationException | TokenExpiredException $e) {
            throw $e;
        }
    }
}
