<?php

declare(strict_types=1);

namespace App\Errors;

use App\Errors\ErrorCode as ApiErrorCode;
use App\Errors\ErrorMessage as Emsg;

class Error
{
    public static function missingMandatoryHttpHeader(string $httpHeader): array
    {
        return self::getError(
            ApiErrorCode::MISSING_MANDATORY_HTTP_HEADER,
            \sprintf(Emsg::MISSING_MANDATORY_HTTP_HEADER, $httpHeader)
        );
    }

    public static function missingValueForHttpHeader(string $httpHeader): array
    {
        return self::getError(
            ApiErrorCode::MISSING_VALUE_FOR_HTTP_HEADER,
            \sprintf(Emsg::MISSING_VALUE_FOR_HTTP_HEADER, $httpHeader)
        );
    }

    public static function invalidValueForHttpHeader(string $httpHeader): array
    {
        return self::getError(
            ApiErrorCode::INVALID_VALUE_FOR_HTTP_HEADER,
            \sprintf(Emsg::INVALID_VALUE_FOR_HTTP_HEADER, $httpHeader)
        );
    }

    public static function usageOfIncorrectHttpMethod(string $givenHttpMethod, string $expectedHttpMethod): array
    {
        return self::getError(
            ApiErrorCode::USAGE_OF_INCORRECT_HTTP_METHOD,
            \sprintf(Emsg::USAGE_OF_INCORRECT_HTTP_METHOD, $givenHttpMethod, $expectedHttpMethod)
        );
    }

    public static function missingMandatoryUrlParameter(string $urlParameter): array
    {
        return self::getError(
            ApiErrorCode::MISSING_MANDATORY_URL_PARAMETER,
            \sprintf(Emsg::MISSING_MANDATORY_URL_PARAMETER, $urlParameter)
        );
    }

    public static function missingValueForUrlParameter(string $urlParameter): array
    {
        return self::getError(
            ApiErrorCode::MISSING_VALUE_FOR_URL_PARAMETER,
            \sprintf(Emsg::MISSING_VALUE_FOR_URL_PARAMETER, $urlParameter)
        );
    }

    public static function noDataFoundForUrlParameter(string $urlParameter): array
    {
        return self::getError(
            ApiErrorCode::NO_DATA_FOUND_FOR_URL_PARAMETER,
            \sprintf(Emsg::NO_DATA_FOUND_FOR_URL_PARAMETER, $urlParameter)
        );
    }

    public static function missingJsonInRequestBody(): array
    {
        return self::getError(
            ApiErrorCode::MISSING_JSON_IN_REQUEST_BODY,
            Emsg::MISSING_JSON_IN_REQUEST_BODY
        );
    }

    public static function malformedJsonInRequestBody(): array
    {
        return self::getError(
            ApiErrorCode::MALFORMED_JSON_IN_REQUEST_BODY,
            Emsg::MALFORMED_JSON_IN_REQUEST_BODY
        );
    }

    public static function missingMandatoryPropertyInRequestBody(string $property): array
    {
        return self::getError(
            ApiErrorCode::MISSING_MANDATORY_PROPERTY_IN_REQUEST_BODY,
            \sprintf(Emsg::MISSING_MANDATORY_PROPERTY_IN_REQUEST_BODY, $property)
        );
    }

    public static function expectedDifferentDataTypeInRequestBody(string $expectedDataType, string $property, string $givenDataType): array
    {
        return self::getError(
            ApiErrorCode::EXPECTED_DIFFERENT_DATA_TYPE_IN_REQUEST_BODY,
            \sprintf(Emsg::EXPECTED_DIFFERENT_DATA_TYPE_IN_REQUEST_BODY, $expectedDataType, $property, $givenDataType)
        );
    }

    public static function expectedDifferentValueInRequestBody(string $property, string $givenValue, string $givenValueDescription): array
    {
        return self::getError(
            ApiErrorCode::EXPECTED_DIFFERENT_VALUE_IN_REQUEST_BODY,
            \sprintf(Emsg::EXPECTED_DIFFERENT_VALUE_IN_REQUEST_BODY, $property, $givenValue, $givenValueDescription)
        );
    }

    public static function noDataFoundForPropertyInRequestBody(string $property): array
    {
        return self::getError(
            ApiErrorCode::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY,
            \sprintf(Emsg::NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY, $property)
        );
    }

    public static function valueIsAlreadyTaken(string $property): array
    {
        return self::getError(
            ApiErrorCode::VALUE_IS_ALREADY_TAKEN,
            \sprintf(Emsg::VALUE_IS_ALREADY_TAKEN, $property)
        );
    }

    public static function numericValueMustBeGreater(string $property, float $expectedValue, float $givenValue): array
    {
        return self::getError(
            ApiErrorCode::NUMERIC_VALUE_MUST_BE_GREATER,
            \sprintf(Emsg::NUMERIC_VALUE_MUST_BE_GREATER, $property, $expectedValue, $givenValue)
        );
    }

    public static function malformedEmail(): array
    {
        return self::getError(
            ApiErrorCode::MALFORMED_EMAIL,
            Emsg::MALFORMED_EMAIL
        );
    }

    public static function malformedDateTime(string $property): array
    {
        return self::getError(
            ApiErrorCode::MALFORMED_DATETIME,
            \sprintf(Emsg::MALFORMED_DATETIME, $property)
        );
    }

    public static function tokenHasExpired(): array
    {
        return self::getError(
            ApiErrorCode::TOKEN_HAS_EXPIRED,
            Emsg::TOKEN_HAS_EXPIRED
        );
    }

    public static function malformedUuid(): array
    {
        return self::getError(
            ApiErrorCode::MALFORMED_UUID,
            Emsg::MALFORMED_UUID
        );
    }

    public static function givenDateTimeDoesNotMakeAnySense(string $dateTime): array
    {
        return self::getError(
            ApiErrorCode::GIVEN_DATETIME_DOES_NOT_MAKE_ANY_SENSE,
            \sprintf(Emsg::GIVEN_DATETIME_DOES_NOT_MAKE_ANY_SENSE, $dateTime)
        );
    }

    public static function dateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo(string $dateTimeOne, string $dateTimeTwo): array
    {
        return self::getError(
            ApiErrorCode::DATETIME_IN_ONE_CANNOT_BE_GREATER_OR_EQUAL_TO_DATETIME_IN_TWO,
            \sprintf(Emsg::DATETIME_IN_ONE_CANNOT_BE_GREATER_OR_EQUAL_TO_DATETIME_IN_TWO, $dateTimeOne, $dateTimeTwo)
        );
    }

    public static function selectedUserIsNotTeacher(): array
    {
        return self::getError(
            ApiErrorCode::SELECTED_USER_IS_NOT_TEACHER,
            Emsg::SELECTED_USER_IS_NOT_TEACHER
        );
    }

    public static function userIsNotTeacherSoPriceMustNotBeSet(): array
    {
        return self::getError(
            ApiErrorCode::USER_IS_NOT_TEACHER_SO_PRICE_MUST_NOT_BE_SET,
            Emsg::USER_IS_NOT_TEACHER_SO_PRICE_MUST_NOT_BE_SET
        );
    }

    public static function userIsTeacherSoPriceMustBeSet(): array
    {
        return self::getError(
            ApiErrorCode::USER_IS_TEACHER_SO_PRICE_MUST_BE_SET,
            Emsg::USER_IS_TEACHER_SO_PRICE_MUST_BE_SET
        );
    }

    public static function couldNotGenerateUniqueValue(string $property, int $tries): array
    {
        return self::getError(
            ApiErrorCode::COULD_NOT_GENERATE_UNIQUE_VALUE,
            \sprintf(Emsg::COULD_NOT_GENERATE_UNIQUE_VALUE, $property, $tries)
        );
    }

    public static function cannotSubscribeToInactiveCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_SUBSCRIBE_TO_INACTIVE_COURSE,
            Emsg::CANNOT_SUBSCRIBE_TO_INACTIVE_COURSE
        );
    }

    public static function cannotSubscribeToOngoingOrEndedCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_SUBSCRIBE_TO_ONGOING_OR_ENDED_COURSE,
            Emsg::CANNOT_SUBSCRIBE_TO_ONGOING_OR_ENDED_COURSE
        );
    }

    public static function cannotUnsubscribeFromOngoingOrEndedCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_UNSUBSCRIBE_FROM_ONGOING_OR_ENDED_COURSE,
            Emsg::CANNOT_UNSUBSCRIBE_FROM_ONGOING_OR_ENDED_COURSE
        );
    }

    public static function toAcceptThisRequestTheUserHasToBeTeacher(): array
    {
        return self::getError(
            ApiErrorCode::TO_ACCEPT_THIS_REQUEST_THE_USER_HAS_TO_BE_TEACHER,
            Emsg::TO_ACCEPT_THIS_REQUEST_THE_USER_HAS_TO_BE_TEACHER
        );
    }

    public static function toDeleteTheLessonTheUserHasToBeTeacher(): array
    {
        return self::getError(
            ApiErrorCode::TO_DELETE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER,
            Emsg::TO_DELETE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER
        );
    }

    public static function toDeleteTheCourseTheUserHasToBeTeacher(): array
    {
        return self::getError(
            ApiErrorCode::TO_DELETE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER,
            Emsg::TO_DELETE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER
        );
    }

    public static function cannotDeleteLessonFromOngoingOrEndedCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_DELETE_LESSON_FROM_ONGOING_OR_ENDED_COURSE,
            Emsg::CANNOT_DELETE_LESSON_FROM_ONGOING_OR_ENDED_COURSE
        );
    }

    public static function cannotDeleteOngoingOrEndedCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_DELETE_ONGOING_OR_ENDED_COURSE,
            Emsg::CANNOT_DELETE_ONGOING_OR_ENDED_COURSE
        );
    }

    public static function attemptToLogIntoAnUnconfirmedUserAccount(): array
    {
        return self::getError(
            ApiErrorCode::ATTEMPT_TO_LOG_INTO_AN_UNCONFIRMED_USER_ACCOUNT,
            Emsg::ATTEMPT_TO_LOG_INTO_AN_UNCONFIRMED_USER_ACCOUNT
        );
    }

    public static function cannotDeleteTeacherWithOngoingCourses(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_DELETE_TEACHER_WITH_ONGOING_COURSES,
            Emsg::CANNOT_DELETE_TEACHER_WITH_ONGOING_COURSES
        );
    }

    public static function cannotDeleteStudentWhichIsSubscribedToOngoingCourses(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_DELETE_STUDENT_WHICH_IS_SUBSCRIBED_TO_ONGOING_COURSES,
            Emsg::CANNOT_DELETE_STUDENT_WHICH_IS_SUBSCRIBED_TO_ONGOING_COURSES
        );
    }

    public static function tryToExecuteTheLastRequestAgainPlease(string $property): array
    {
        return self::getError(
            ApiErrorCode::TRY_TO_EXECUTE_THE_LAST_REQUEST_AGAIN_PLEASE,
            \sprintf(Emsg::TRY_TO_EXECUTE_THE_LAST_REQUEST_AGAIN_PLEASE, $property)
        );
    }

    public static function sessionFoundByApiTokenButItsClientIdDoesNotMatch(): array
    {
        return self::getError(
            ApiErrorCode::SESSION_FOUND_BY_API_TOKEN_BUT_ITS_CLIENT_ID_DOES_NOT_MATCH,
            Emsg::SESSION_FOUND_BY_API_TOKEN_BUT_ITS_CLIENT_ID_DOES_NOT_MATCH
        );
    }

    public static function sessionIsLocked(): array
    {
        return self::getError(
            ApiErrorCode::SESSION_IS_LOCKED,
            Emsg::SESSION_IS_LOCKED
        );
    }

    public static function incorrectPasswordHasBeenEntered(int $remainingAttempts): array
    {
        return self::getError(
            ApiErrorCode::INCORRECT_PASSWORD_HAS_BEEN_ENTERED,
            \sprintf(Emsg::INCORRECT_PASSWORD_HAS_BEEN_ENTERED, $remainingAttempts)
        );
    }

    public static function accountHasBeenLocked(int $failures): array
    {
        return self::getError(
            ApiErrorCode::ACCOUNT_HAS_BEEN_LOCKED,
            \sprintf(Emsg::ACCOUNT_HAS_BEEN_LOCKED, $failures)
        );
    }

    public static function securityCodeHasBeenGenerated(int $failures): array
    {
        return self::getError(
            ApiErrorCode::SECURITY_CODE_HAS_BEEN_GENERATED,
            \sprintf(Emsg::SECURITY_CODE_HAS_BEEN_GENERATED, $failures)
        );
    }

    public static function incorrectSecurityCodeHasBeenEntered(int $remainingAttempts): array
    {
        return self::getError(
            ApiErrorCode::INCORRECT_SECURITY_CODE_HAS_BEEN_ENTERED,
            \sprintf(Emsg::INCORRECT_SECURITY_CODE_HAS_BEEN_ENTERED, $remainingAttempts)
        );
    }

    public static function securityCodeHasBeenGeneratedAgain(int $failures): array
    {
        return self::getError(
            ApiErrorCode::SECURITY_CODE_HAS_BEEN_GENERATED_AGAIN,
            \sprintf(Emsg::SECURITY_CODE_HAS_BEEN_GENERATED_AGAIN, $failures)
        );
    }

    public static function securityCodeHasExpired(): array
    {
        return self::getError(
            ApiErrorCode::SECURITY_CODE_HAS_EXPIRED,
            Emsg::SECURITY_CODE_HAS_EXPIRED
        );
    }

    public static function userIsTryingToUseAnotherEmailAddress(): array
    {
        return self::getError(
            ApiErrorCode::USER_IS_TRYING_TO_USE_ANOTHER_EMAIL_ADDRESS,
            Emsg::USER_IS_TRYING_TO_USE_ANOTHER_EMAIL_ADDRESS
        );
    }

    public static function oldApiClientIdIsDifferentThanTheOneInCurrentSession(): array
    {
        return self::getError(
            ApiErrorCode::OLD_API_CLIENT_ID_IS_DIFFERENT_THAN_THE_ONE_IN_CURRENT_SESSION,
            Emsg::OLD_API_CLIENT_ID_IS_DIFFERENT_THAN_THE_ONE_IN_CURRENT_SESSION
        );
    }

    public static function userDoesNotHaveAnySecurityCode(): array
    {
        return self::getError(
            ApiErrorCode::USER_DOES_NOT_HAVE_ANY_SECURITY_CODE,
            Emsg::USER_DOES_NOT_HAVE_ANY_SECURITY_CODE
        );
    }

    public static function cannotSubscribeToYourOwnCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_SUBSCRIBE_TO_YOUR_OWN_COURSE,
            Emsg::CANNOT_SUBSCRIBE_TO_YOUR_OWN_COURSE
        );
    }

    public static function cannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_UNSUBSCRIBE_FROM_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO,
            Emsg::CANNOT_UNSUBSCRIBE_FROM_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO
        );
    }

    public static function toUpdateTheLessonTheUserHasToBeTeacher(): array
    {
        return self::getError(
            ApiErrorCode::TO_UPDATE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER,
            Emsg::TO_UPDATE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER
        );
    }

    public static function toUpdateTheCourseTheUserHasToBeTeacher(): array
    {
        return self::getError(
            ApiErrorCode::TO_UPDATE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER,
            Emsg::TO_UPDATE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER
        );
    }

    public static function cannotUpdateLessonFromOngoingOrEndedCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_UPDATE_LESSON_FROM_ONGOING_OR_ENDED_COURSE,
            Emsg::CANNOT_UPDATE_LESSON_FROM_ONGOING_OR_ENDED_COURSE
        );
    }

    public static function cannotUpdateOngoingOrEndedCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_UPDATE_ONGOING_OR_ENDED_COURSE,
            Emsg::CANNOT_UPDATE_ONGOING_OR_ENDED_COURSE
        );
    }

    public static function stringLengthMustNotBeLonger(string $property, int $length): array
    {
        return self::getError(
            ApiErrorCode::STRING_LENGTH_MUST_NOT_BE_LONGER,
            \sprintf(Emsg::STRING_LENGTH_MUST_NOT_BE_LONGER, $property, $length)
        );
    }

    public static function numberSizeMustNotBeHigher(string $property, int $size): array
    {
        return self::getError(
            ApiErrorCode::NUMBER_SIZE_MUST_NOT_BE_HIGHER,
            \sprintf(Emsg::NUMBER_SIZE_MUST_NOT_BE_HIGHER, $property, $size)
        );
    }

    public static function selectedTimezoneIsInvalid(string $timezone): array
    {
        return self::getError(
            ApiErrorCode::SELECTED_TIMEZONE_IS_INVALID,
            \sprintf(Emsg::SELECTED_TIMEZONE_IS_INVALID, $timezone)
        );
    }

    public static function cannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_SEND_PAYMENT_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO,
            Emsg::CANNOT_SEND_PAYMENT_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO
        );
    }

    public static function cannotSendPaymentForTheSameCourseAgain(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_SEND_PAYMENT_FOR_THE_SAME_COURSE_AGAIN,
            Emsg::CANNOT_SEND_PAYMENT_FOR_THE_SAME_COURSE_AGAIN
        );
    }

    public static function cannotSendPaymentForTheOngoingOrEndedCourse(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_SEND_PAYMENT_FOR_THE_ONGOING_OR_ENDED_COURSE,
            Emsg::CANNOT_SEND_PAYMENT_FOR_THE_ONGOING_OR_ENDED_COURSE
        );
    }

    public static function cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_REQUEST_REFUND_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO,
            Emsg::CANNOT_REQUEST_REFUND_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO
        );
    }

    public static function cannotRequestRefundForThePaymentWhichYouDidNotMake(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_YOU_DID_NOT_MAKE,
            Emsg::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_YOU_DID_NOT_MAKE
        );
    }

    public static function cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_OPENED_REFUND_REQUEST,
            Emsg::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_OPENED_REFUND_REQUEST
        );
    }

    public static function cannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest(): array
    {
        return self::getError(
            ApiErrorCode::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_CLOSED_REFUND_REQUEST,
            Emsg::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_CLOSED_REFUND_REQUEST
        );
    }

    public static function tryingToGetLessonListForCourseWhichIsNotYours(): array
    {
        return self::getError(
            ApiErrorCode::TRYING_TO_GET_LESSON_LIST_FOR_COURSE_WHICH_IS_NOT_YOURS,
            Emsg::TRYING_TO_GET_LESSON_LIST_FOR_COURSE_WHICH_IS_NOT_YOURS
        );
    }

    public static function tryingToUploadAvatarImageOfUnsupportedType(): array
    {
        return self::getError(
            ApiErrorCode::TRYING_TO_UPLOAD_AVATAR_IMAGE_OF_UNSUPPORTED_TYPE,
            Emsg::TRYING_TO_UPLOAD_AVATAR_IMAGE_OF_UNSUPPORTED_TYPE
        );
    }

    private static function getError(int $code, string $message): array
    {
        return [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];
    }
}
