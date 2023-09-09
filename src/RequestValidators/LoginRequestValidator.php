<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\AuthenticationFailureException;
use App\Exceptions\LockAccountException;
use App\Exceptions\RequestValidationException;
use App\Exceptions\SecurityCodeHasToBeGeneratedException;
use Symfony\Component\HttpFoundation\HeaderBag;

class LoginRequestValidator
{
    private RequestValidator $requestValidator;

    public function __construct(RequestValidator $requestValidator)
    {
        $this->requestValidator = $requestValidator;
    }

    /**
     * @throws LockAccountException
     * @throws AuthenticationFailureException
     * @throws RequestValidationException
     * @throws SecurityCodeHasToBeGeneratedException
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
            $this->requestValidator->checkIfApiClientIdHttpHeaderIsMissing($headers);
            $this->requestValidator->checkIfApiClientIdHttpHeaderIsEmpty($headers);
            $this->requestValidator->checkIfHttpMethodIsPost($method);
            $this->requestValidator->checkIfThereIsMissingJsonInRequestBody($requestBody);
            $this->requestValidator->checkIfThereIsMalformedJsonInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsMalformedInRequestBody($data);
            $this->requestValidator->checkIfAnyDataForPropertyEmailWereFound($data);
            $this->requestValidator->checkIfPropertyPasswordIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyPasswordIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyPasswordIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfAccountHasBeenLocked($data);
            $this->requestValidator->checkIfIncorrectPasswordHasBeenEntered($data);
            $this->requestValidator->checkIfSecurityCodeHasToBeGenerated($data);
            $this->requestValidator->checkIfUserIsAttemptingToLogIntoAnUnconfirmedAccount($data);
        } catch (LockAccountException | AuthenticationFailureException | RequestValidationException | SecurityCodeHasToBeGeneratedException $e) {
            throw $e;
        }
    }
}
