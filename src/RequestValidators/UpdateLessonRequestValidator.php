<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class UpdateLessonRequestValidator
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
            $this->requestValidator->checkIfAnyDataForUrlParameterLessonIdWereFound($parameters);
            $this->requestValidator->checkIfThereIsMissingJsonInRequestBody($requestBody);
            $this->requestValidator->checkIfThereIsMalformedJsonInRequestBody($data);
            $this->requestValidator->checkIfPropertyNameIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyNameIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyNameIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfStringLengthIsNotLongerForPropertyNameInRequestBody($data);
            $this->requestValidator->checkIfPropertyFromIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyFromIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyFromIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfDateTimeIsMalformedInPropertyFromInRequestBody($data);
            $this->requestValidator->checkIfGivenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody($data);
            $this->requestValidator->checkIfPropertyToIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyToIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyToIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfDateTimeIsMalformedInPropertyToInRequestBody($data);
            $this->requestValidator->checkIfGivenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody($data);
            $this->requestValidator->checkIfDateTimeInFromIsGreaterOrEqualToDateTimeInTo($data);
            $this->requestValidator->checkIfPropertyCourseIdIsMissingInRequestBody($data);
            $this->requestValidator->checkIfPropertyCourseIdIsOfCorrectDataTypeInRequestBody($data);
            $this->requestValidator->checkIfPropertyCourseIdIsNonEmptyStringInRequestBody($data);
            $this->requestValidator->checkIfUuidForPropertyCourseIdIsMalformedInRequestBody($data);
            $this->requestValidator->checkIfAnyDataWereFoundInCoursesForPropertyCourseId($data);
            $this->requestValidator->checkIfTheUserIsTeacherToAcceptThisRequest($headers);
            $this->requestValidator->checkIfTheUserIsTeacherToUpdateTheLesson($headers, $parameters);
            $this->requestValidator->checkIfCannotUpdateLessonFromOngoingOrEndedCourse($parameters);
        } catch (RequestValidationException $e) {
            throw $e;
        }
    }
}
