<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SecurityCodeConfirmationFailureException;
use App\Exceptions\SecurityCodeExpiredException;
use App\Exceptions\SecurityCodeHasToBeGeneratedAgainException;
use Symfony\Component\HttpFoundation\HeaderBag;

class SecurityCodeConfirmationRequestValidator
{
    private RequestValidator $requestValidator;

    public function __construct(RequestValidator $requestValidator)
    {
        $this->requestValidator = $requestValidator;
    }

    /**
     * @throws RequestValidationException
     * @throws SecurityCodeConfirmationFailureException
     * @throws SecurityCodeExpiredException
     * @throws SecurityCodeHasToBeGeneratedAgainException
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
            $this->requestValidator->checkIfAnyDataForPropertyEmailWereFound($data);
            $this->requestValidator->checkIfPropertySecurityCodeIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertySecurityCodeIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertySecurityCodeIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfUserDoesHaveAnySecurityCode($data);
            $this->requestValidator->checkIfSecurityCodeHasToBeGeneratedAgain($data);
            $this->requestValidator->checkIfIncorrectSecurityCodeHasBeenEntered($data);
            $this->requestValidator->checkIfSecurityCodeHasExpired($data);
        } catch (RequestValidationException | SecurityCodeConfirmationFailureException | SecurityCodeExpiredException | SecurityCodeHasToBeGeneratedAgainException $e) {
            throw $e;
        }
    }
}
