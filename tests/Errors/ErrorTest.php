<?php

declare(strict_types=1);

namespace Tests\App\Errors;

use App\Errors\Error;
use PHPUnit\Framework\TestCase;

final class ErrorTest extends TestCase
{
    public function testMissingMandatoryHttpHeader(): void
    {
        $this->doAssert(
            function (): array {
                return Error::missingMandatoryHttpHeader('Api-Key');
            },
            1,
            "Missing mandatory 'Api-Key' http header."
        );
    }

    public function testMissingValueForHttpHeader(): void
    {
        $this->doAssert(
            function (): array {
                return Error::missingValueForHttpHeader('Api-Key');
            },
            2,
            "Missing value for 'Api-Key' http header."
        );
    }

    public function testInvalidValueForHttpHeader(): void
    {
        $this->doAssert(
            function (): array {
                return Error::invalidValueForHttpHeader('Api-Key');
            },
            3,
            "Invalid value for 'Api-Key' http header."
        );
    }

    public function testUsageOfIncorrectHttpMethod(): void
    {
        $this->doAssert(
            function (): array {
                return Error::usageOfIncorrectHttpMethod('GET', 'POST');
            },
            4,
            "Usage of incorrect http method 'GET'. 'POST' was expected."
        );
    }

    public function testMissingMandatoryUrlParameter(): void
    {
        $this->doAssert(
            function (): array {
                return Error::missingMandatoryUrlParameter('email');
            },
            5,
            "Missing mandatory 'email' url parameter."
        );
    }

    public function testMissingValueForUrlParameter(): void
    {
        $this->doAssert(
            function (): array {
                return Error::missingValueForUrlParameter('email');
            },
            6,
            "Missing value for 'email' url parameter."
        );
    }

    public function testNoDataFoundForUrlParameter(): void
    {
        $this->doAssert(
            function (): array {
                return Error::noDataFoundForUrlParameter('email');
            },
            7,
            "No data found for 'email' url parameter."
        );
    }

    public function testMissingJsonInRequestBody(): void
    {
        $this->doAssert(
            function (): array {
                return Error::missingJsonInRequestBody();
            },
            8,
            'Missing JSON in request body.'
        );
    }

    public function testMalformedJsonInRequestBody(): void
    {
        $this->doAssert(
            function (): array {
                return Error::malformedJsonInRequestBody();
            },
            9,
            'Malformed JSON in request body.'
        );
    }

    public function testMissingMandatoryPropertyInRequestBody(): void
    {
        $this->doAssert(
            function (): array {
                return Error::missingMandatoryPropertyInRequestBody('email');
            },
            10,
            "Missing mandatory property 'email' in request body."
        );
    }

    public function testExpectedDifferentDataTypeInRequestBody(): void
    {
        $this->doAssert(
            function (): array {
                return Error::expectedDifferentDataTypeInRequestBody('string', 'email', 'integer');
            },
            11,
            "Expected string in 'email', but got integer in request body."
        );
    }

    public function testExpectedDifferentValueInRequestBody(): void
    {
        $this->doAssert(
            function (): array {
                return Error::expectedDifferentValueInRequestBody('email', '""', 'empty string');
            },
            12,
            "Expected value in 'email', but got \"\" (empty string) in request body."
        );
    }

    public function testNoDataFoundForPropertyInRequestBody(): void
    {
        $this->doAssert(
            function (): array {
                return Error::noDataFoundForPropertyInRequestBody('email');
            },
            13,
            "No data found for 'email' in request body."
        );
    }

    public function testValueIsAlreadyTaken(): void
    {
        $this->doAssert(
            function (): array {
                return Error::valueIsAlreadyTaken('email');
            },
            14,
            "Value for 'email' in request body is already taken."
        );
    }

    public function testNumericValueMustBeGreater(): void
    {
        $this->doAssert(
            function (): array {
                return Error::numericValueMustBeGreater('email', 0, 0);
            },
            15,
            "Numeric value for 'email' must be greater than 0, but got 0."
        );
    }

    public function testMalformedEmail(): void
    {
        $this->doAssert(
            function (): array {
                return Error::malformedEmail();
            },
            16,
            'Malformed email.'
        );
    }

    public function testMalformedDateTime(): void
    {
        $this->doAssert(
            function (): array {
                return Error::malformedDateTime('from');
            },
            17,
            "Malformed datetime in 'from'. Expected string e.g. '2000-12-24 20:30:00'."
        );
    }

    public function testTokenHasExpired(): void
    {
        $this->doAssert(
            function (): array {
                return Error::tokenHasExpired();
            },
            18,
            'Token has expired. New email was sent.'
        );
    }

    public function testMalformedUuid(): void
    {
        $this->doAssert(
            function (): array {
                return Error::malformedUuid();
            },
            19,
            'Malformed uuid.'
        );
    }

    public function testGivenDateTimeDoesNotMakeAnySense(): void
    {
        $this->doAssert(
            function (): array {
                return Error::givenDateTimeDoesNotMakeAnySense('2000-99-99 99:99:99');
            },
            20,
            "Given datetime '2000-99-99 99:99:99' does not make any sense."
        );
    }

    public function testDateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo(): void
    {
        $this->doAssert(
            function (): array {
                return Error::dateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo('from', 'to');
            },
            21,
            "Datetime in 'from' cannot be greater or equal to datetime in 'to'."
        );
    }

    public function testSelectedUserIsNotTeacher(): void
    {
        $this->doAssert(
            function (): array {
                return Error::selectedUserIsNotTeacher();
            },
            22,
            'Selected user is not teacher.'
        );
    }

    public function testUserIsNotTeacherSoPriceMustNotBeSet(): void
    {
        $this->doAssert(
            function (): array {
                return Error::userIsNotTeacherSoPriceMustNotBeSet();
            },
            23,
            'User is not teacher, so price must not be set.'
        );
    }

    public function testUserIsTeacherSoPriceMustBeSet(): void
    {
        $this->doAssert(
            function (): array {
                return Error::userIsTeacherSoPriceMustBeSet();
            },
            24,
            'User is teacher, so price must be set.'
        );
    }

    public function testCouldNotGenerateUniqueValue(): void
    {
        $this->doAssert(
            function (): array {
                return Error::couldNotGenerateUniqueValue('token', 5);
            },
            25,
            "Could not generate unique value for 'token' in 5 tries."
        );
    }

    public function testCannotSubscribeToInactiveCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotSubscribeToInactiveCourse();
            },
            26,
            'Cannot subscribe to inactive course.'
        );
    }

    public function testCannotSubscribeToOngoingOrEndedCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotSubscribeToOngoingOrEndedCourse();
            },
            27,
            'Cannot subscribe to ongoing or ended course.'
        );
    }

    public function testCannotUnsubscribeFromOngoingOrEndedCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotUnsubscribeFromOngoingOrEndedCourse();
            },
            28,
            'Cannot unsubscribe from ongoing or ended course.'
        );
    }

    public function testToAcceptThisRequestTheUserHasToBeTeacher(): void
    {
        $this->doAssert(
            function (): array {
                return Error::toAcceptThisRequestTheUserHasToBeTeacher();
            },
            29,
            'To accept this request the user has to be teacher.'
        );
    }

    public function testToDeleteTheLessonTheUserHasToBeTeacher(): void
    {
        $this->doAssert(
            function (): array {
                return Error::toDeleteTheLessonTheUserHasToBeTeacher();
            },
            30,
            "To delete the lesson the user has to be teacher in the given lesson's course."
        );
    }

    public function testToDeleteTheCourseTheUserHasToBeTeacher(): void
    {
        $this->doAssert(
            function (): array {
                return Error::toDeleteTheCourseTheUserHasToBeTeacher();
            },
            31,
            'To delete the course the user has to be teacher in the given course.'
        );
    }

    public function testCannotDeleteLessonFromOngoingOrEndedCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotDeleteLessonFromOngoingOrEndedCourse();
            },
            32,
            'Cannot delete lesson from ongoing or ended course.'
        );
    }

    public function testCannotDeleteOngoingOrEndedCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotDeleteOngoingOrEndedCourse();
            },
            33,
            'Cannot delete ongoing or ended course.'
        );
    }

    public function testAttemptToLogIntoAnUnconfirmedUserAccount(): void
    {
        $this->doAssert(
            function (): array {
                return Error::attemptToLogIntoAnUnconfirmedUserAccount();
            },
            34,
            'Attempt to log into an unconfirmed user account.'
        );
    }

    public function testCannotDeleteTeacherWithOngoingCourses(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotDeleteTeacherWithOngoingCourses();
            },
            35,
            'Cannot delete teacher with ongoing courses.'
        );
    }

    public function testCannotDeleteStudentWhichIsSubscribedToOngoingCourses(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotDeleteStudentWhichIsSubscribedToOngoingCourses();
            },
            36,
            'Cannot delete student which is subscribed to ongoing courses.'
        );
    }

    public function testTryToExecuteTheLastRequestAgainPlease(): void
    {
        $this->doAssert(
            function (): array {
                return Error::tryToExecuteTheLastRequestAgainPlease('token');
            },
            37,
            "Try to execute the last request again please. There was duplicity in generated value for 'token'."
        );
    }

    public function testSessionFoundByApiTokenButItsClientIdDoesNotMatch(): void
    {
        $this->doAssert(
            function (): array {
                return Error::sessionFoundByApiTokenButItsClientIdDoesNotMatch();
            },
            38,
            'Session found by api token but its client id does not match with the one provided in header Api-Client-Id. Session has been locked for security reasons.'
        );
    }

    public function testSessionIsLocked(): void
    {
        $this->doAssert(
            function (): array {
                return Error::sessionIsLocked();
            },
            39,
            'Session is locked. User must re-authenticate.'
        );
    }

    public function testIncorrectPasswordHasBeenEntered(): void
    {
        $this->doAssert(
            function (): array {
                return Error::incorrectPasswordHasBeenEntered(2);
            },
            40,
            'Incorrect password has been entered. 2 attempt(s) left.'
        );
    }

    public function testAccountHasBeenLocked(): void
    {
        $this->doAssert(
            function (): array {
                return Error::accountHasBeenLocked(3);
            },
            41,
            'Incorrect password has been entered 3 or more times in a row. Account has been locked for security reasons.'
        );
    }

    public function testSecurityCodeHasBeenGenerated(): void
    {
        $this->doAssert(
            function (): array {
                return Error::securityCodeHasBeenGenerated(3);
            },
            42,
            "User's authentication was successful, but since there were 3 or more failed login attempts in a row in the past, a security code has been generated and sent on user's email address."
        );
    }

    public function testIncorrectSecurityCodeHasBeenEntered(): void
    {
        $this->doAssert(
            function (): array {
                return Error::incorrectSecurityCodeHasBeenEntered(2);
            },
            43,
            'Incorrect security code has been entered. 2 attempt(s) left.'
        );
    }

    public function testSecurityCodeHasBeenGeneratedAgain(): void
    {
        $this->doAssert(
            function (): array {
                return Error::securityCodeHasBeenGeneratedAgain(3);
            },
            44,
            "Incorrect security code has been entered 3 or more times in a row. New security code has been generated and sent on user's email address."
        );
    }

    public function testSecurityCodeHasExpired(): void
    {
        $this->doAssert(
            function (): array {
                return Error::securityCodeHasExpired();
            },
            45,
            "Security code has expired. New security code has been generated and sent on user's email address."
        );
    }

    public function testUserIsTryingToUseAnotherEmailAddress(): void
    {
        $this->doAssert(
            function (): array {
                return Error::userIsTryingToUseAnotherEmailAddress();
            },
            46,
            "Re-authentication failed. User is trying to use another user's email address."
        );
    }

    public function testOldApiClientIdIsDifferentThanTheOneInCurrentSession(): void
    {
        $this->doAssert(
            function (): array {
                return Error::oldApiClientIdIsDifferentThanTheOneInCurrentSession();
            },
            47,
            'Re-authentication failed. Value of old api client id in request body is different than the one in current session.'
        );
    }

    public function testUserDoesNotHaveAnySecurityCode(): void
    {
        $this->doAssert(
            function (): array {
                return Error::userDoesNotHaveAnySecurityCode();
            },
            48,
            'User does not have any security code.'
        );
    }

    public function testCannotSubscribeToYourOwnCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotSubscribeToYourOwnCourse();
            },
            49,
            'Cannot subscribe to your own course.'
        );
    }

    public function testCannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo();
            },
            50,
            'Cannot unsubscribe from course to which you are not subscribed to.'
        );
    }

    public function testToUpdateTheLessonTheUserHasToBeTeacher(): void
    {
        $this->doAssert(
            function (): array {
                return Error::toUpdateTheLessonTheUserHasToBeTeacher();
            },
            51,
            "To update the lesson the user has to be teacher in the given lesson's course."
        );
    }

    public function testToUpdateTheCourseTheUserHasToBeTeacher(): void
    {
        $this->doAssert(
            function (): array {
                return Error::toUpdateTheCourseTheUserHasToBeTeacher();
            },
            52,
            'To update the course the user has to be teacher in the given course.'
        );
    }

    public function testCannotUpdateLessonFromOngoingOrEndedCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotUpdateLessonFromOngoingOrEndedCourse();
            },
            53,
            'Cannot update lesson from ongoing or ended course.'
        );
    }

    public function testCannotUpdateOngoingOrEndedCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotUpdateOngoingOrEndedCourse();
            },
            54,
            'Cannot update ongoing or ended course.'
        );
    }

    public function testStringLengthMustNotBeLonger(): void
    {
        $this->doAssert(
            function (): array {
                return Error::stringLengthMustNotBeLonger('firstName', 255);
            },
            55,
            "String length of property 'firstName' must not be longer than 255 characters."
        );
    }

    public function testNumberSizeMustNotBeHigher(): void
    {
        $this->doAssert(
            function (): array {
                return Error::numberSizeMustNotBeHigher('price', 4294967295);
            },
            56,
            "Number size of property 'price' must not be higher than 4294967295."
        );
    }

    public function testSelectedTimezoneIsInvalid(): void
    {
        $this->doAssert(
            function (): array {
                return Error::selectedTimezoneIsInvalid('XYZ');
            },
            57,
            "Selected timezone 'XYZ' is invalid."
        );
    }

    public function testCannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo();
            },
            58,
            'Cannot send payment for the course to which you are not subscribed to.'
        );
    }

    public function testCannotSendPaymentForTheSameCourseAgain(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotSendPaymentForTheSameCourseAgain();
            },
            59,
            'Cannot send payment for the same course again.'
        );
    }

    public function testCannotSendPaymentForTheOngoingOrEndedCourse(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotSendPaymentForTheOngoingOrEndedCourse();
            },
            60,
            'Cannot send payment for the ongoing or ended course.'
        );
    }

    public function testCannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo();
            },
            61,
            'Cannot request refund for the course to which you are not subscribed to.'
        );
    }

    public function testCannotRequestRefundForThePaymentWhichYouDidNotMake(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotRequestRefundForThePaymentWhichYouDidNotMake();
            },
            62,
            'Cannot request refund for the payment which you did not make.'
        );
    }

    public function testCannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest();
            },
            63,
            'Cannot request refund for the payment which already has opened refund request.'
        );
    }

    public function testCannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest(): void
    {
        $this->doAssert(
            function (): array {
                return Error::cannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest();
            },
            64,
            'Cannot request refund for the payment which already has closed refund request.'
        );
    }

    public function testTryingToGetLessonListForCourseWhichIsNotYours(): void
    {
        $this->doAssert(
            function (): array {
                return Error::tryingToGetLessonListForCourseWhichIsNotYours();
            },
            65,
            'Trying to get lesson list for course which is not yours.'
        );
    }

    public function testTryingToUploadAvatarImageOfUnsupportedType(): void
    {
        $this->doAssert(
            function (): array {
                return Error::tryingToUploadAvatarImageOfUnsupportedType();
            },
            66,
            'Trying to upload avatar image of unsupported type.'
        );
    }

    private function doAssert(callable $cb, int $errorCode, string $errorMessage): void
    {
        $result = \call_user_func($cb);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $error = $result['error'];
        $this->assertArrayHasKey('code', $error);
        $this->assertSame($errorCode, $error['code']);
        $this->assertArrayHasKey('message', $error);
        $this->assertSame($errorMessage, $error['message']);
    }
}
