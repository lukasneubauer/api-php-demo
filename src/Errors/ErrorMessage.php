<?php

declare(strict_types=1);

namespace App\Errors;

class ErrorMessage
{
    /** @var string */
    public const MISSING_MANDATORY_HTTP_HEADER = "Missing mandatory '%s' http header.";

    /** @var string */
    public const MISSING_VALUE_FOR_HTTP_HEADER = "Missing value for '%s' http header.";

    /** @var string */
    public const INVALID_VALUE_FOR_HTTP_HEADER = "Invalid value for '%s' http header.";

    /** @var string */
    public const USAGE_OF_INCORRECT_HTTP_METHOD = "Usage of incorrect http method '%s'. '%s' was expected.";

    /** @var string */
    public const MISSING_MANDATORY_URL_PARAMETER = "Missing mandatory '%s' url parameter.";

    /** @var string */
    public const MISSING_VALUE_FOR_URL_PARAMETER = "Missing value for '%s' url parameter.";

    /** @var string */
    public const NO_DATA_FOUND_FOR_URL_PARAMETER = "No data found for '%s' url parameter.";

    /** @var string */
    public const MISSING_JSON_IN_REQUEST_BODY = 'Missing JSON in request body.';

    /** @var string */
    public const MALFORMED_JSON_IN_REQUEST_BODY = 'Malformed JSON in request body.';

    /** @var string */
    public const MISSING_MANDATORY_PROPERTY_IN_REQUEST_BODY = "Missing mandatory property '%s' in request body.";

    /** @var string */
    public const EXPECTED_DIFFERENT_DATA_TYPE_IN_REQUEST_BODY = "Expected %s in '%s', but got %s in request body.";

    /** @var string */
    public const EXPECTED_DIFFERENT_VALUE_IN_REQUEST_BODY = "Expected value in '%s', but got %s (%s) in request body.";

    /** @var string */
    public const NO_DATA_FOUND_FOR_PROPERTY_IN_REQUEST_BODY = "No data found for '%s' in request body.";

    /** @var string */
    public const VALUE_IS_ALREADY_TAKEN = "Value for '%s' in request body is already taken.";

    /** @var string */
    public const NUMERIC_VALUE_MUST_BE_GREATER = "Numeric value for '%s' must be greater than %s, but got %s.";

    /** @var string */
    public const MALFORMED_EMAIL = 'Malformed email.';

    /** @var string */
    public const MALFORMED_DATETIME = "Malformed datetime in '%s'. Expected string e.g. '2000-12-24 20:30:00'.";

    /** @var string */
    public const TOKEN_HAS_EXPIRED = 'Token has expired. New email was sent.';

    /** @var string */
    public const MALFORMED_UUID = 'Malformed uuid.';

    /** @var string */
    public const GIVEN_DATETIME_DOES_NOT_MAKE_ANY_SENSE = "Given datetime '%s' does not make any sense.";

    /** @var string */
    public const DATETIME_IN_ONE_CANNOT_BE_GREATER_OR_EQUAL_TO_DATETIME_IN_TWO = "Datetime in '%s' cannot be greater or equal to datetime in '%s'.";

    /** @var string */
    public const SELECTED_USER_IS_NOT_TEACHER = 'Selected user is not teacher.';

    /** @var string */
    public const USER_IS_NOT_TEACHER_SO_PRICE_MUST_NOT_BE_SET = 'User is not teacher, so price must not be set.';

    /** @var string */
    public const USER_IS_TEACHER_SO_PRICE_MUST_BE_SET = 'User is teacher, so price must be set.';

    /** @var string */
    public const COULD_NOT_GENERATE_UNIQUE_VALUE = "Could not generate unique value for '%s' in %s tries.";

    /** @var string */
    public const CANNOT_SUBSCRIBE_TO_INACTIVE_COURSE = 'Cannot subscribe to inactive course.';

    /** @var string */
    public const CANNOT_SUBSCRIBE_TO_ONGOING_OR_ENDED_COURSE = 'Cannot subscribe to ongoing or ended course.';

    /** @var string */
    public const CANNOT_UNSUBSCRIBE_FROM_ONGOING_OR_ENDED_COURSE = 'Cannot unsubscribe from ongoing or ended course.';

    /** @var string */
    public const TO_ACCEPT_THIS_REQUEST_THE_USER_HAS_TO_BE_TEACHER = 'To accept this request the user has to be teacher.';

    /** @var string */
    public const TO_DELETE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER = "To delete the lesson the user has to be teacher in the given lesson's course.";

    /** @var string */
    public const TO_DELETE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER = 'To delete the course the user has to be teacher in the given course.';

    /** @var string */
    public const CANNOT_DELETE_LESSON_FROM_ONGOING_OR_ENDED_COURSE = 'Cannot delete lesson from ongoing or ended course.';

    /** @var string */
    public const CANNOT_DELETE_ONGOING_OR_ENDED_COURSE = 'Cannot delete ongoing or ended course.';

    /** @var string */
    public const ATTEMPT_TO_LOG_INTO_AN_UNCONFIRMED_USER_ACCOUNT = 'Attempt to log into an unconfirmed user account.';

    /** @var string */
    public const CANNOT_DELETE_TEACHER_WITH_ONGOING_COURSES = 'Cannot delete teacher with ongoing courses.';

    /** @var string */
    public const CANNOT_DELETE_STUDENT_WHICH_IS_SUBSCRIBED_TO_ONGOING_COURSES = 'Cannot delete student which is subscribed to ongoing courses.';

    /** @var string */
    public const TRY_TO_EXECUTE_THE_LAST_REQUEST_AGAIN_PLEASE = "Try to execute the last request again please. There was duplicity in generated value for '%s'.";

    /** @var string */
    public const SESSION_FOUND_BY_API_TOKEN_BUT_ITS_CLIENT_ID_DOES_NOT_MATCH = 'Session found by api token but its client id does not match with the one provided in header Api-Client-Id. Session has been locked for security reasons.';

    /** @var string */
    public const SESSION_IS_LOCKED = 'Session is locked. User must re-authenticate.';

    /** @var string */
    public const INCORRECT_PASSWORD_HAS_BEEN_ENTERED = 'Incorrect password has been entered. %s attempt(s) left.';

    /** @var string */
    public const ACCOUNT_HAS_BEEN_LOCKED = 'Incorrect password has been entered %s or more times in a row. Account has been locked for security reasons.';

    /** @var string */
    public const SECURITY_CODE_HAS_BEEN_GENERATED = "User's authentication was successful, but since there were %s or more failed login attempts in a row in the past, a security code has been generated and sent on user's email address.";

    /** @var string */
    public const INCORRECT_SECURITY_CODE_HAS_BEEN_ENTERED = 'Incorrect security code has been entered. %s attempt(s) left.';

    /** @var string */
    public const SECURITY_CODE_HAS_BEEN_GENERATED_AGAIN = "Incorrect security code has been entered %s or more times in a row. New security code has been generated and sent on user's email address.";

    /** @var string */
    public const SECURITY_CODE_HAS_EXPIRED = "Security code has expired. New security code has been generated and sent on user's email address.";

    /** @var string */
    public const USER_IS_TRYING_TO_USE_ANOTHER_EMAIL_ADDRESS = "Re-authentication failed. User is trying to use another user's email address.";

    /** @var string */
    public const OLD_API_CLIENT_ID_IS_DIFFERENT_THAN_THE_ONE_IN_CURRENT_SESSION = 'Re-authentication failed. Value of old api client id in request body is different than the one in current session.';

    /** @var string */
    public const USER_DOES_NOT_HAVE_ANY_SECURITY_CODE = 'User does not have any security code.';

    /** @var string */
    public const CANNOT_SUBSCRIBE_TO_YOUR_OWN_COURSE = 'Cannot subscribe to your own course.';

    /** @var string */
    public const CANNOT_UNSUBSCRIBE_FROM_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO = 'Cannot unsubscribe from course to which you are not subscribed to.';

    /** @var string */
    public const TO_UPDATE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER = "To update the lesson the user has to be teacher in the given lesson's course.";

    /** @var string */
    public const TO_UPDATE_THE_COURSE_THE_USER_HAS_TO_BE_TEACHER = 'To update the course the user has to be teacher in the given course.';

    /** @var string */
    public const CANNOT_UPDATE_LESSON_FROM_ONGOING_OR_ENDED_COURSE = 'Cannot update lesson from ongoing or ended course.';

    /** @var string */
    public const CANNOT_UPDATE_ONGOING_OR_ENDED_COURSE = 'Cannot update ongoing or ended course.';

    /** @var string */
    public const STRING_LENGTH_MUST_NOT_BE_LONGER = "String length of property '%s' must not be longer than %s characters.";

    /** @var string */
    public const NUMBER_SIZE_MUST_NOT_BE_HIGHER = "Number size of property '%s' must not be higher than %s.";

    /** @var string */
    public const SELECTED_TIMEZONE_IS_INVALID = "Selected timezone '%s' is invalid.";

    /** @var string */
    public const CANNOT_SEND_PAYMENT_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO = 'Cannot send payment for the course to which you are not subscribed to.';

    /** @var string */
    public const CANNOT_SEND_PAYMENT_FOR_THE_SAME_COURSE_AGAIN = 'Cannot send payment for the same course again.';

    /** @var string */
    public const CANNOT_SEND_PAYMENT_FOR_THE_ONGOING_OR_ENDED_COURSE = 'Cannot send payment for the ongoing or ended course.';

    /** @var string */
    public const CANNOT_REQUEST_REFUND_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO = 'Cannot request refund for the course to which you are not subscribed to.';

    /** @var string */
    public const CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_YOU_DID_NOT_MAKE = 'Cannot request refund for the payment which you did not make.';

    /** @var string */
    public const CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_OPENED_REFUND_REQUEST = 'Cannot request refund for the payment which already has opened refund request.';

    /** @var string */
    public const CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_CLOSED_REFUND_REQUEST = 'Cannot request refund for the payment which already has closed refund request.';

    /** @var string */
    public const TRYING_TO_GET_LESSON_LIST_FOR_COURSE_WHICH_IS_NOT_YOURS = 'Trying to get lesson list for course which is not yours.';

    /** @var string */
    public const TRYING_TO_UPLOAD_AVATAR_IMAGE_OF_UNSUPPORTED_TYPE = 'Trying to upload avatar image of unsupported type.';
}
