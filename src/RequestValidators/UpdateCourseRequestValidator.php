<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class UpdateCourseRequestValidator
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
        ParameterBag $parameters,
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
            $this->requestValidator->checkIfHttpMethodIsPatch($method);
            $this->requestValidator->checkIfUuidIsMalformedInIdUrlParameter($parameters);
            $this->requestValidator->checkIfAnyDataForUrlParameterCourseIdWereFound($parameters);
            $this->requestValidator->checkIfThereIsMissingJsonInRequestBody($requestBody);
            $this->requestValidator->checkIfThereIsMalformedJsonInRequestBody($data);
            $this->requestValidator->checkIfPropertyNameIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyNameIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyNameIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfStringLengthIsNotLongerForPropertyNameInRequestBody($data);
            $this->requestValidator->checkIfPropertySubjectIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertySubjectIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertySubjectIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfStringLengthIsNotLongerForPropertySubjectInRequestBody($data);
            $this->requestValidator->checkIfPropertyPriceIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyPriceIsNumberInRequestBody($data);
            $this->requestValidator->checkIfPropertyPriceIsGreaterThanZeroInRequestBody($data);
            $this->requestValidator->checkIfNumberSizeIsNotHigherForPropertyPriceInRequestBody($data);
            $this->requestValidator->checkIfPropertyIsActiveIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyIsActiveIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfTheUserIsTeacherToAcceptThisRequest($headers);
            $this->requestValidator->checkIfTheUserIsTeacherToUpdateTheCourse($headers, $parameters);
            $this->requestValidator->checkIfCannotUpdateOngoingOrEndedCourse($parameters);
        } catch (RequestValidationException $e) {
            throw $e;
        }
    }
}
