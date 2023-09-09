<?php

declare(strict_types=1);

namespace App\Errors;

class ErrorCode
{
    /** @var int */
    public const MISSING_MANDATORY_HTTP_HEADER = 1;

    /** @var int */
    public const MISSING_VALUE_FOR_HTTP_HEADER = 2;

    /** @var int */
    public const INVALID_VALUE_FOR_HTTP_HEADER = 3;

    /** @var int */
    public const USAGE_OF_INCORRECT_HTTP_METHOD = 4;

    /** @var int */
    public const MISSING_MANDATORY_URL_PARAMETER = 5;

    /** @var int */
    public const MISSING_VALUE_FOR_URL_PARAMETER = 6;

    /** @var int */
    public const NO_DATA_FOUND_FOR_URL_PARAMETER = 7;

    /** @var int */
    public const MISSING_JSON_IN_REQUEST_BODY = 8;

    /** @var int */
    public const MALFORMED_JSON_IN_REQUEST_BODY = 9;

    /** @var int */
    public const MISSING_MANDATORY_PROPERTY_IN_REQUEST_BODY = 10;

    /** @var int */
    public const EXPECTED_DIFFERENT_DATA_TYPE_IN_REQUEST_BODY = 11;

    /** @var int */
    public const EXPECTED_DIFFERENT_VALUE_IN_REQUEST_BODY = 12;

    /** @var int */
    public const NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY = 13;

    /** @var int */
    public const VALUE_IS_ALREADY_TAKEN = 14;

    /** @var int */
    public const NUMERIC_VALUE_MUST_BE_GREATER = 15;

    /** @var int */
    public const MALFORMED_EMAIL = 16;

    /** @var int */
    public const MALFORMED_DATETIME = 17;

    /** @var int */
    public const TOKEN_HAS_EXPIRED = 18;

    /** @var int */
    public const MALFORMED_UUID = 19;

    /** @var int */
    public const GIVEN_DATETIME_DOES_NOT_MAKE_ANY_SENSE = 20;

    /** @var int */
    public const DATETIME_IN_ONE_CANNOT_BE_GREATER_OR_EQUAL_TO_DATETIME_IN_TWO = 21;

    /** @var int */
    public const SELECTED_USER_IS_NOT_TEACHER = 22;

    /** @var int */
    public const USER_IS_NOT_TEACHER_SO_PRICE_MUST_NOT_BE_SET = 23;

    /** @var int */
    public const USER_IS_TEACHER_SO_PRICE_MUST_BE_SET = 24;

    /** @var int */
    public const COULD_NOT_GENERATE_UNIQUE_VALUE = 25;

    /** @var int */
    public const CANNOT_SUBSCRIBE_TO_INACTIVE_COURSE = 26;

    /** @var int */
    public const CANNOT_SUBSCRIBE_TO_ONGOING_OR_ENDED_COURSE = 27;

    /** @var int */
    public const CANNOT_UNSUBSCRIBE_FROM_ONGOING_OR_ENDED_COURSE = 28;

    /** @var int */
    public const TO_ACCEPT_THIS_REQUEST_THE_USER_HAS_TO_BE_TEACHER = 29;

    /** @var int */
    public const TO_DELETE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER = 30;

    /** @var int */
    public const TO_DELETE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER = 31;

    /** @var int */
    public const CANNOT_DELETE_LESSON_FROM_ONGOING_OR_ENDED_COURSE = 32;

    /** @var int */
    public const CANNOT_DELETE_ONGOING_OR_ENDED_COURSE = 33;

    /** @var int */
    public const ATTEMPT_TO_LOG_INTO_AN_UNCONFIRMED_USER_ACCOUNT = 34;

    /** @var int */
    public const CANNOT_DELETE_TEACHER_WITH_ONGOING_COURSES = 35;

    /** @var int */
    public const CANNOT_DELETE_STUDENT_WHICH_IS_SUBSCRIBED_TO_ONGOING_COURSES = 36;

    /** @var int */
    public const TRY_TO_EXECUTE_THE_LAST_REQUEST_AGAIN_PLEASE = 37;

    /** @var int */
    public const SESSION_FOUND_BY_API_TOKEN_BUT_ITS_CLIENT_ID_DOES_NOT_MATCH = 38;

    /** @var int */
    public const SESSION_IS_LOCKED = 39;

    /** @var int */
    public const INCORRECT_PASSWORD_HAS_BEEN_ENTERED = 40;

    /** @var int */
    public const ACCOUNT_HAS_BEEN_LOCKED = 41;

    /** @var int */
    public const SECURITY_CODE_HAS_BEEN_GENERATED = 42;

    /** @var int */
    public const INCORRECT_SECURITY_CODE_HAS_BEEN_ENTERED = 43;

    /** @var int */
    public const SECURITY_CODE_HAS_BEEN_GENERATED_AGAIN = 44;

    /** @var int */
    public const SECURITY_CODE_HAS_EXPIRED = 45;

    /** @var int */
    public const USER_IS_TRYING_TO_USE_ANOTHER_EMAIL_ADDRESS = 46;

    /** @var int */
    public const OLD_API_CLIENT_ID_IS_DIFFERENT_THAN_THE_ONE_IN_CURRENT_SESSION = 47;

    /** @var int */
    public const USER_DOES_NOT_HAVE_ANY_SECURITY_CODE = 48;

    /** @var int */
    public const CANNOT_SUBSCRIBE_TO_YOUR_OWN_COURSE = 49;

    /** @var int */
    public const CANNOT_UNSUBSCRIBE_FROM_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO = 50;

    /** @var int */
    public const TO_UPDATE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER = 51;

    /** @var int */
    public const TO_UPDATE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER = 52;

    /** @var int */
    public const CANNOT_UPDATE_LESSON_FROM_ONGOING_OR_ENDED_COURSE = 53;

    /** @var int */
    public const CANNOT_UPDATE_ONGOING_OR_ENDED_COURSE = 54;

    /** @var int */
    public const STRING_LENGTH_MUST_NOT_BE_LONGER = 55;

    /** @var int */
    public const NUMBER_SIZE_MUST_NOT_BE_HIGHER = 56;

    /** @var int */
    public const SELECTED_TIMEZONE_IS_INVALID = 57;

    /** @var int */
    public const CANNOT_SEND_PAYMENT_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO = 58;

    /** @var int */
    public const CANNOT_SEND_PAYMENT_FOR_THE_SAME_COURSE_AGAIN = 59;

    /** @var int */
    public const CANNOT_SEND_PAYMENT_FOR_THE_ONGOING_OR_ENDED_COURSE = 60;

    /** @var int */
    public const CANNOT_REQUEST_REFUND_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO = 61;

    /** @var int */
    public const CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_YOU_DID_NOT_MAKE = 62;

    /** @var int */
    public const CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_OPENED_REFUND_REQUEST = 63;

    /** @var int */
    public const CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_CLOSED_REFUND_REQUEST = 64;

    /** @var int */
    public const TRYING_TO_GET_LESSON_LIST_FOR_COURSE_WHICH_IS_NOT_YOURS = 65;

    /** @var int */
    public const TRYING_TO_UPLOAD_AVATAR_IMAGE_OF_UNSUPPORTED_TYPE = 66;
}
