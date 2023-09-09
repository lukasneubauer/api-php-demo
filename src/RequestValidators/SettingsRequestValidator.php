<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use Symfony\Component\HttpFoundation\HeaderBag;

class SettingsRequestValidator
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
            $this->requestValidator->checkIfApiClientIdHttpHeaderIsInvalid($headers);
            $this->requestValidator->checkIfApiTokenHttpHeaderIsMissing($headers);
            $this->requestValidator->checkIfApiTokenHttpHeaderIsEmpty($headers);
            $this->requestValidator->checkIfApiTokenHttpHeaderIsInvalid($headers);
            $this->requestValidator->checkIfSessionFoundByApiTokenButItsClientIdDoesNotMatch($headers);
            $this->requestValidator->checkIfSessionIsLocked($headers);
            $this->requestValidator->checkIfHttpMethodIsPost($method);
            $this->requestValidator->checkIfThereIsMissingJsonInRequestBody($requestBody);
            $this->requestValidator->checkIfThereIsMalformedJsonInRequestBody($data);
            $this->requestValidator->checkIfPropertyFirstNameIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyFirstNameIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyFirstNameIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfStringLengthIsNotLongerForPropertyFirstNameInRequestBody($data);
            $this->requestValidator->checkIfPropertyLastNameIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyLastNameIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyLastNameIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfStringLengthIsNotLongerForPropertyLastNameInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfPropertyEmailIsMalformedInRequestBody($data);
            $this->requestValidator->checkIfStringLengthIsNotLongerForPropertyEmailInRequestBody($data);
            $this->requestValidator->checkIfPropertyPasswordIsMissingInRequestBody($data);
            $this->requestValidator->checkIfNullablePropertyPasswordIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyPasswordIsNonEmptyStringOrNullInRequestBody($data);
            $this->requestValidator->checkIfPropertyIsTeacherIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyIsTeacherIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyTimezoneIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyTimezoneIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyTimezoneIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfSelectedTimezoneIsInvalid($data);
        } catch (RequestValidationException $e) {
            throw $e;
        }
    }
}
