<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use Symfony\Component\HttpFoundation\HeaderBag;

class ResetPasswordRequestValidator
{
    private RequestValidator $requestValidator;

    public function __construct(RequestValidator $requestValidator)
    {
        $this->requestValidator = $requestValidator;
    }

    /**
     * @throws RequestValidationException
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
            $this->requestValidator->checkIfPropertyUserIdIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyUserIdIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyUserIdIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfAnyDataWereFoundInUsersForPropertyUserId($data);
            $this->requestValidator->checkIfPropertyPasswordIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyPasswordIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyPasswordIsNonEmptyStringInRequestBody($data);
        } catch (RequestValidationException $e) {
            throw $e;
        }
    }
}
