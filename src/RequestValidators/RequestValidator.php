<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Exceptions\AuthenticationFailureException;
use App\Exceptions\LockAccountException;
use App\Exceptions\RequestValidationException;
use App\Exceptions\SecurityCodeConfirmationFailureException;
use App\Exceptions\SecurityCodeExpiredException;
use App\Exceptions\SecurityCodeHasToBeGeneratedAgainException;
use App\Exceptions\SecurityCodeHasToBeGeneratedException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Exceptions\TokenExpiredException;
use App\Exceptions\ValidationException;
use App\Validators\AccountHasBeenLocked;
use App\Validators\AttemptToLogIntoAnUnconfirmedUserAccount;
use App\Validators\CannotDeleteLessonFromOngoingOrEndedCourse;
use App\Validators\CannotDeleteOngoingOrEndedCourse;
use App\Validators\CannotDeleteStudentWhichIsSubscribedToOngoingCourses;
use App\Validators\CannotDeleteTeacherWithOngoingCourses;
use App\Validators\CannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo;
use App\Validators\CannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest;
use App\Validators\CannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest;
use App\Validators\CannotRequestRefundForThePaymentWhichYouDidNotMake;
use App\Validators\CannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo;
use App\Validators\CannotSendPaymentForTheOngoingOrEndedCourse;
use App\Validators\CannotSendPaymentForTheSameCourseAgain;
use App\Validators\CannotSubscribeToInactiveCourse;
use App\Validators\CannotSubscribeToOngoingOrEndedCourse;
use App\Validators\CannotSubscribeToYourOwnCourse;
use App\Validators\CannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo;
use App\Validators\CannotUnsubscribeFromOngoingOrEndedCourse;
use App\Validators\CannotUpdateLessonFromOngoingOrEndedCourse;
use App\Validators\CannotUpdateOngoingOrEndedCourse;
use App\Validators\DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo;
use App\Validators\ExpectedDifferentDataTypeForNullablePropertyInRequestBody;
use App\Validators\ExpectedDifferentDataTypeForPropertyInRequestBody;
use App\Validators\ExpectedNonEmptyStringInRequestBody;
use App\Validators\ExpectedNonEmptyStringOrNullInRequestBody;
use App\Validators\ExpectedNumberForPropertyPriceInRequestBody;
use App\Validators\ExpectedNumberOrNullForPropertyPriceInRequestBody;
use App\Validators\GivenDateTimeDoesNotMakeAnySenseInRequestBody;
use App\Validators\IncorrectPasswordHasBeenEntered;
use App\Validators\IncorrectSecurityCodeHasBeenEntered;
use App\Validators\InvalidValueForApiClientIdHttpHeader;
use App\Validators\InvalidValueForApiKeyHttpHeader;
use App\Validators\InvalidValueForApiTokenHttpHeader;
use App\Validators\MalformedDateTimeInRequestBody;
use App\Validators\MalformedEmailInRequestBody;
use App\Validators\MalformedEmailInUrlParameter;
use App\Validators\MalformedJsonInRequestBody;
use App\Validators\MalformedUuidInRequestBody;
use App\Validators\MalformedUuidInUrlParameter;
use App\Validators\MissingJsonInRequestBody;
use App\Validators\MissingMandatoryHttpHeader;
use App\Validators\MissingMandatoryPropertyInRequestBody;
use App\Validators\MissingMandatoryUrlParameter;
use App\Validators\MissingValueForHttpHeader;
use App\Validators\MissingValueForUrlParameter;
use App\Validators\NoDataFoundForCourseIdUrlParameter;
use App\Validators\NoDataFoundForEmailUrlParameter;
use App\Validators\NoDataFoundForLessonIdUrlParameter;
use App\Validators\NoDataFoundForPropertyCourseIdInRequestBody;
use App\Validators\NoDataFoundForPropertyEmailInRequestBody;
use App\Validators\NoDataFoundForPropertyLessonIdInRequestBody;
use App\Validators\NoDataFoundForPropertyTeacherIdInRequestBody;
use App\Validators\NoDataFoundForPropertyUserIdInRequestBody;
use App\Validators\NoDataFoundForTokenUrlParameter;
use App\Validators\NoDataFoundInCoursesForPropertyCourseIdInRequestBody;
use App\Validators\NoDataFoundInUsersForPropertyUserIdInRequestBody;
use App\Validators\NumberSizeMustNotBeHigher;
use App\Validators\NumericValueMustBeGreaterInRequestBody;
use App\Validators\NumericValueMustBeGreaterOrNullInRequestBody;
use App\Validators\OldApiClientIdIsDifferentThanTheOneInCurrentSession;
use App\Validators\SecurityCodeHasBeenGenerated;
use App\Validators\SecurityCodeHasBeenGeneratedAgain;
use App\Validators\SecurityCodeHasExpired;
use App\Validators\SelectedTimezoneIsInvalid;
use App\Validators\SelectedUserIsNotTeacher;
use App\Validators\SessionFoundByApiTokenButItsClientIdDoesNotMatch;
use App\Validators\SessionIsLocked;
use App\Validators\StringLengthMustNotBeLonger;
use App\Validators\ToAcceptThisRequestTheUserHasToBeTeacher;
use App\Validators\ToDeleteTheCourseTheUserHasToBeTeacher;
use App\Validators\ToDeleteTheLessonTheUserHasToBeTeacher;
use App\Validators\TokenExpired;
use App\Validators\TokenInUrlParameterExpired;
use App\Validators\ToUpdateTheCourseTheUserHasToBeTeacher;
use App\Validators\ToUpdateTheLessonTheUserHasToBeTeacher;
use App\Validators\TryingToGetLessonListForCourseWhichIsNotYours;
use App\Validators\TryingToUploadAvatarImageOfUnsupportedType;
use App\Validators\TryingToUploadAvatarImageOfUnsupportedTypeIfNotNull;
use App\Validators\UsageOfIncorrectHttpMethod;
use App\Validators\UserDoesNotHaveAnySecurityCode;
use App\Validators\UserIsNotTeacherSoPriceMustNotBeSet;
use App\Validators\UserIsTeacherSoPriceMustBeSet;
use App\Validators\UserIsTryingToUseAnotherEmailAddress;
use App\Validators\UserNotFoundByCredentials;
use App\Validators\UserNotFoundByEmailCredentials;
use App\Validators\UserNotFoundByEmailCredentialsInUrlParameters;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class RequestValidator
{
    private bool $performRequestValidation;

    private MissingMandatoryHttpHeader $missingMandatoryApiKeyHttpHeader;

    private MissingMandatoryHttpHeader $missingMandatoryApiClientIdHttpHeader;

    private MissingMandatoryHttpHeader $missingMandatoryApiTokenHttpHeader;

    private MissingValueForHttpHeader $missingValueForApiKeyHttpHeader;

    private MissingValueForHttpHeader $missingValueForApiClientIdHttpHeader;

    private MissingValueForHttpHeader $missingValueForApiTokenHttpHeader;

    private InvalidValueForApiKeyHttpHeader $invalidValueForApiKeyHttpHeader;

    private InvalidValueForApiClientIdHttpHeader $invalidValueForApiClientIdHttpHeader;

    private InvalidValueForApiTokenHttpHeader $invalidValueForApiTokenHttpHeader;

    private UsageOfIncorrectHttpMethod $checkIfHttpMethodIsGet;

    private UsageOfIncorrectHttpMethod $checkIfHttpMethodIsPost;

    private UsageOfIncorrectHttpMethod $checkIfHttpMethodIsDelete;

    private UsageOfIncorrectHttpMethod $checkIfHttpMethodIsPatch;

    private MissingMandatoryUrlParameter $missingMandatoryIdUrlParameter;

    private MissingMandatoryUrlParameter $missingMandatoryEmailUrlParameter;

    private MissingMandatoryUrlParameter $missingMandatoryTokenUrlParameter;

    private MissingValueForUrlParameter $missingValueForIdUrlParameter;

    private MissingValueForUrlParameter $missingValueForEmailUrlParameter;

    private MissingValueForUrlParameter $missingValueForTokenUrlParameter;

    private NoDataFoundForCourseIdUrlParameter $noDataFoundForCourseIdUrlParameter;

    private NoDataFoundForLessonIdUrlParameter $noDataFoundForLessonIdUrlParameter;

    private NoDataFoundForEmailUrlParameter $noDataFoundForEmailUrlParameter;

    private NoDataFoundForTokenUrlParameter $noDataFoundForTokenUrlParameter;

    private UserNotFoundByEmailCredentialsInUrlParameters $userNotFoundByEmailCredentialsInUrlParameters;

    private MissingJsonInRequestBody $missingJsonInRequestBody;

    private MalformedJsonInRequestBody $malformedJsonInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIdInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyCourseIdInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyTeacherIdInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyUserIdInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyFirstNameInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyLastNameInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyAvatarInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyEmailInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyPasswordInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIsTeacherInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIsStudentInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyTimezoneInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyTokenInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyNameInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertySubjectInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyFromInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyToInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyOldApiClientIdInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertySecurityCodeInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIsActiveInRequestBody;

    private MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyPriceInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIdInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyCourseIdInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyTeacherIdInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyUserIdInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyFirstNameInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyLastNameInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyAvatarInRequestBody;

    private ExpectedDifferentDataTypeForNullablePropertyInRequestBody $expectedDifferentDataTypeForNullablePropertyAvatarInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyEmailInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyPasswordInRequestBody;

    private ExpectedDifferentDataTypeForNullablePropertyInRequestBody $expectedDifferentDataTypeForNullablePropertyPasswordInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIsTeacherInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIsStudentInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyTimezoneInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyTokenInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyNameInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertySubjectInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyFromInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyToInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyOldApiClientIdInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertySecurityCodeInRequestBody;

    private ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIsActiveInRequestBody;

    private ExpectedNumberForPropertyPriceInRequestBody $expectedNumberForPropertyPriceInRequestBody;

    private ExpectedNumberOrNullForPropertyPriceInRequestBody $expectedNumberOrNullForPropertyPriceInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInIdInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInCourseIdInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInTeacherIdInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInUserIdInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInFirstNameInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInLastNameInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInAvatarInRequestBody;

    private ExpectedNonEmptyStringOrNullInRequestBody $expectedNonEmptyStringOrNullInAvatarInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInEmailInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInPasswordInRequestBody;

    private ExpectedNonEmptyStringOrNullInRequestBody $expectedNonEmptyStringOrNullInPasswordInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInTimezoneInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInTokenInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInNameInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInSubjectInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInFromInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInToInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInOldApiClientIdInRequestBody;

    private ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInSecurityCodeInRequestBody;

    private NoDataFoundInCoursesForPropertyCourseIdInRequestBody $noDataFoundInCoursesForPropertyCourseIdInRequestBody;

    private NoDataFoundInUsersForPropertyUserIdInRequestBody $noDataFoundInUsersForPropertyUserIdInRequestBody;

    private NoDataFoundForPropertyCourseIdInRequestBody $noDataFoundForPropertyCourseIdInRequestBody;

    private NoDataFoundForPropertyLessonIdInRequestBody $noDataFoundForPropertyLessonIdInRequestBody;

    private NoDataFoundForPropertyTeacherIdInRequestBody $noDataFoundForPropertyTeacherIdInRequestBody;

    private NoDataFoundForPropertyEmailInRequestBody $noDataFoundForPropertyEmailInRequestBody;

    private NoDataFoundForPropertyUserIdInRequestBody $noDataFoundForPropertyUserIdInRequestBody;

    private UserNotFoundByCredentials $userNotFoundByCredentials;

    private UserNotFoundByEmailCredentials $userNotFoundByEmailCredentials;

    private NumericValueMustBeGreaterInRequestBody $numericValueInPriceMustBeGreaterThanZeroInRequestBody;

    private NumericValueMustBeGreaterOrNullInRequestBody $numericValueInPriceMustBeGreaterThanZeroOrNullInRequestBody;

    private MalformedEmailInUrlParameter $malformedEmailInUrlParameter;

    private MalformedEmailInRequestBody $malformedEmailInRequestBody;

    private MalformedDateTimeInRequestBody $malformedDateTimeInPropertyFromInRequestBody;

    private MalformedDateTimeInRequestBody $malformedDateTimeInPropertyToInRequestBody;

    private TokenExpired $tokenExpired;

    private TokenInUrlParameterExpired $tokenInUrlParameterExpired;

    private MalformedUuidInRequestBody $malformedUuidForPropertyIdInRequestBody;

    private MalformedUuidInRequestBody $malformedUuidForPropertyCourseIdInRequestBody;

    private MalformedUuidInRequestBody $malformedUuidForPropertyTeacherIdInRequestBody;

    private MalformedUuidInUrlParameter $malformedUuidInIdUrlParameter;

    private GivenDateTimeDoesNotMakeAnySenseInRequestBody $givenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody;

    private GivenDateTimeDoesNotMakeAnySenseInRequestBody $givenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody;

    private DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo $dateTimeInFromCannotBeGreaterOrEqualToDateTimeInTo;

    private SelectedUserIsNotTeacher $selectedUserIsNotTeacher;

    private UserIsNotTeacherSoPriceMustNotBeSet $userIsNotTeacherSoPriceMustNotBeSet;

    private UserIsTeacherSoPriceMustBeSet $userIsTeacherSoPriceMustBeSet;

    private CannotSubscribeToInactiveCourse $cannotSubscribeToInactiveCourse;

    private CannotSubscribeToOngoingOrEndedCourse $cannotSubscribeToOngoingOrEndedCourse;

    private CannotUnsubscribeFromOngoingOrEndedCourse $cannotUnsubscribeFromOngoingOrEndedCourse;

    private ToAcceptThisRequestTheUserHasToBeTeacher $toAcceptThisRequestTheUserHasToBeTeacher;

    private ToDeleteTheLessonTheUserHasToBeTeacher $toDeleteTheLessonTheUserHasToBeTeacher;

    private ToDeleteTheCourseTheUserHasToBeTeacher $toDeleteTheCourseTheUserHasToBeTeacher;

    private CannotDeleteLessonFromOngoingOrEndedCourse $cannotDeleteLessonFromOngoingOrEndedCourse;

    private CannotDeleteOngoingOrEndedCourse $cannotDeleteOngoingOrEndedCourse;

    private AttemptToLogIntoAnUnconfirmedUserAccount $attemptToLogIntoAnUnconfirmedUserAccount;

    private CannotDeleteTeacherWithOngoingCourses $cannotDeleteTeacherWithOngoingCourses;

    private CannotDeleteStudentWhichIsSubscribedToOngoingCourses $cannotDeleteStudentWhichIsSubscribedToOngoingCourses;

    private SessionFoundByApiTokenButItsClientIdDoesNotMatch $sessionFoundByApiTokenButItsClientIdDoesNotMatch;

    private SessionIsLocked $sessionIsLocked;

    private IncorrectPasswordHasBeenEntered $incorrectPasswordHasBeenEntered;

    private AccountHasBeenLocked $accountHasBeenLocked;

    private SecurityCodeHasBeenGenerated $securityCodeHasBeenGenerated;

    private IncorrectSecurityCodeHasBeenEntered $incorrectSecurityCodeHasBeenEntered;

    private SecurityCodeHasBeenGeneratedAgain $securityCodeHasBeenGeneratedAgain;

    private SecurityCodeHasExpired $securityCodeHasExpired;

    private UserIsTryingToUseAnotherEmailAddress $userIsTryingToUseAnotherEmailAddress;

    private OldApiClientIdIsDifferentThanTheOneInCurrentSession $oldApiClientIdIsDifferentThanTheOneInCurrentSession;

    private UserDoesNotHaveAnySecurityCode $userDoesNotHaveAnySecurityCode;

    private CannotSubscribeToYourOwnCourse $cannotSubscribeToYourOwnCourse;

    private CannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo $cannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo;

    private ToUpdateTheLessonTheUserHasToBeTeacher $toUpdateTheLessonTheUserHasToBeTeacher;

    private ToUpdateTheCourseTheUserHasToBeTeacher $toUpdateTheCourseTheUserHasToBeTeacher;

    private CannotUpdateLessonFromOngoingOrEndedCourse $cannotUpdateLessonFromOngoingOrEndedCourse;

    private CannotUpdateOngoingOrEndedCourse $cannotUpdateOngoingOrEndedCourse;

    private StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyFirstNameInRequestBody;

    private StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyLastNameInRequestBody;

    private StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyEmailInRequestBody;

    private StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyNameInRequestBody;

    private StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertySubjectInRequestBody;

    private NumberSizeMustNotBeHigher $numberSizeMustNotBeHigherForPropertyPriceInRequestBody;

    private SelectedTimezoneIsInvalid $selectedTimezoneIsInvalid;

    private CannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo $cannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo;

    private CannotSendPaymentForTheSameCourseAgain $cannotSendPaymentForTheSameCourseAgain;

    private CannotSendPaymentForTheOngoingOrEndedCourse $cannotSendPaymentForTheOngoingOrEndedCourse;

    private CannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo $cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo;

    private CannotRequestRefundForThePaymentWhichYouDidNotMake $cannotRequestRefundForThePaymentWhichYouDidNotMake;

    private CannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest $cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest;

    private CannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest $cannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest;

    private TryingToGetLessonListForCourseWhichIsNotYours $tryingToGetLessonListForCourseWhichIsNotYours;

    private TryingToUploadAvatarImageOfUnsupportedType $tryingToUploadAvatarImageOfUnsupportedType;

    private TryingToUploadAvatarImageOfUnsupportedTypeIfNotNull $tryingToUploadAvatarImageOfUnsupportedTypeIfNotNull;

    public function __construct(
        bool $performRequestValidation,
        MissingMandatoryHttpHeader $missingMandatoryApiKeyHttpHeader,
        MissingMandatoryHttpHeader $missingMandatoryApiClientIdHttpHeader,
        MissingMandatoryHttpHeader $missingMandatoryApiTokenHttpHeader,
        MissingValueForHttpHeader $missingValueForApiKeyHttpHeader,
        MissingValueForHttpHeader $missingValueForApiClientIdHttpHeader,
        MissingValueForHttpHeader $missingValueForApiTokenHttpHeader,
        InvalidValueForApiKeyHttpHeader $invalidValueForApiKeyHttpHeader,
        InvalidValueForApiClientIdHttpHeader $invalidValueForApiClientIdHttpHeader,
        InvalidValueForApiTokenHttpHeader $invalidValueForApiTokenHttpHeader,
        UsageOfIncorrectHttpMethod $checkIfHttpMethodIsGet,
        UsageOfIncorrectHttpMethod $checkIfHttpMethodIsPost,
        UsageOfIncorrectHttpMethod $checkIfHttpMethodIsDelete,
        UsageOfIncorrectHttpMethod $checkIfHttpMethodIsPatch,
        MissingMandatoryUrlParameter $missingMandatoryIdUrlParameter,
        MissingMandatoryUrlParameter $missingMandatoryEmailUrlParameter,
        MissingMandatoryUrlParameter $missingMandatoryTokenUrlParameter,
        MissingValueForUrlParameter $missingValueForIdUrlParameter,
        MissingValueForUrlParameter $missingValueForEmailUrlParameter,
        MissingValueForUrlParameter $missingValueForTokenUrlParameter,
        NoDataFoundForCourseIdUrlParameter $noDataFoundForCourseIdUrlParameter,
        NoDataFoundForLessonIdUrlParameter $noDataFoundForLessonIdUrlParameter,
        NoDataFoundForEmailUrlParameter $noDataFoundForEmailUrlParameter,
        NoDataFoundForTokenUrlParameter $noDataFoundForTokenUrlParameter,
        UserNotFoundByEmailCredentialsInUrlParameters $userNotFoundByEmailCredentialsInUrlParameters,
        MissingJsonInRequestBody $missingJsonInRequestBody,
        MalformedJsonInRequestBody $malformedJsonInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIdInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyCourseIdInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyTeacherIdInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyUserIdInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyFirstNameInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyLastNameInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyAvatarInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyEmailInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyPasswordInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIsTeacherInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIsStudentInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyTimezoneInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyTokenInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyNameInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertySubjectInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyFromInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyToInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyOldApiClientIdInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertySecurityCodeInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyIsActiveInRequestBody,
        MissingMandatoryPropertyInRequestBody $missingMandatoryPropertyPriceInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIdInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyCourseIdInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyTeacherIdInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyUserIdInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyFirstNameInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyLastNameInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyAvatarInRequestBody,
        ExpectedDifferentDataTypeForNullablePropertyInRequestBody $expectedDifferentDataTypeForNullablePropertyAvatarInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyEmailInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyPasswordInRequestBody,
        ExpectedDifferentDataTypeForNullablePropertyInRequestBody $expectedDifferentDataTypeForNullablePropertyPasswordInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIsTeacherInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIsStudentInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyTimezoneInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyTokenInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyNameInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertySubjectInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyFromInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyToInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyOldApiClientIdInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertySecurityCodeInRequestBody,
        ExpectedDifferentDataTypeForPropertyInRequestBody $expectedDifferentDataTypeForPropertyIsActiveInRequestBody,
        ExpectedNumberForPropertyPriceInRequestBody $expectedNumberForPropertyPriceInRequestBody,
        ExpectedNumberOrNullForPropertyPriceInRequestBody $expectedNumberOrNullForPropertyPriceInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInIdInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInCourseIdInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInTeacherIdInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInUserIdInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInFirstNameInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInLastNameInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInAvatarInRequestBody,
        ExpectedNonEmptyStringOrNullInRequestBody $expectedNonEmptyStringOrNullInAvatarInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInEmailInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInPasswordInRequestBody,
        ExpectedNonEmptyStringOrNullInRequestBody $expectedNonEmptyStringOrNullInPasswordInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInTimezoneInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInTokenInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInNameInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInSubjectInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInFromInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInToInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInOldApiClientIdInRequestBody,
        ExpectedNonEmptyStringInRequestBody $expectedNonEmptyStringInSecurityCodeInRequestBody,
        NoDataFoundInCoursesForPropertyCourseIdInRequestBody $noDataFoundInCoursesForPropertyCourseIdInRequestBody,
        NoDataFoundInUsersForPropertyUserIdInRequestBody $noDataFoundInUsersForPropertyUserIdInRequestBody,
        NoDataFoundForPropertyCourseIdInRequestBody $noDataFoundForPropertyCourseIdInRequestBody,
        NoDataFoundForPropertyLessonIdInRequestBody $noDataFoundForPropertyLessonIdInRequestBody,
        NoDataFoundForPropertyTeacherIdInRequestBody $noDataFoundForPropertyTeacherIdInRequestBody,
        NoDataFoundForPropertyEmailInRequestBody $noDataFoundForPropertyEmailInRequestBody,
        NoDataFoundForPropertyUserIdInRequestBody $noDataFoundForPropertyUserIdInRequestBody,
        UserNotFoundByCredentials $userNotFoundByCredentials,
        UserNotFoundByEmailCredentials $userNotFoundByEmailCredentials,
        NumericValueMustBeGreaterInRequestBody $numericValueInPriceMustBeGreaterThanZeroInRequestBody,
        NumericValueMustBeGreaterOrNullInRequestBody $numericValueInPriceMustBeGreaterThanZeroOrNullInRequestBody,
        MalformedEmailInUrlParameter $malformedEmailInUrlParameter,
        MalformedEmailInRequestBody $malformedEmailInRequestBody,
        MalformedDateTimeInRequestBody $malformedDateTimeInPropertyFromInRequestBody,
        MalformedDateTimeInRequestBody $malformedDateTimeInPropertyToInRequestBody,
        TokenExpired $tokenExpired,
        TokenInUrlParameterExpired $tokenInUrlParameterExpired,
        MalformedUuidInRequestBody $malformedUuidForPropertyIdInRequestBody,
        MalformedUuidInRequestBody $malformedUuidForPropertyCourseIdInRequestBody,
        MalformedUuidInRequestBody $malformedUuidForPropertyTeacherIdInRequestBody,
        MalformedUuidInUrlParameter $malformedUuidInIdUrlParameter,
        GivenDateTimeDoesNotMakeAnySenseInRequestBody $givenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody,
        GivenDateTimeDoesNotMakeAnySenseInRequestBody $givenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody,
        DateTimeInOneCannotBeGreaterOrEqualToDateTimeInTwo $dateTimeInFromCannotBeGreaterOrEqualToDateTimeInTo,
        SelectedUserIsNotTeacher $selectedUserIsNotTeacher,
        UserIsNotTeacherSoPriceMustNotBeSet $userIsNotTeacherSoPriceMustNotBeSet,
        UserIsTeacherSoPriceMustBeSet $userIsTeacherSoPriceMustBeSet,
        CannotSubscribeToInactiveCourse $cannotSubscribeToInactiveCourse,
        CannotSubscribeToOngoingOrEndedCourse $cannotSubscribeToOngoingOrEndedCourse,
        CannotUnsubscribeFromOngoingOrEndedCourse $cannotUnsubscribeFromOngoingOrEndedCourse,
        ToAcceptThisRequestTheUserHasToBeTeacher $toAcceptThisRequestTheUserHasToBeTeacher,
        ToDeleteTheLessonTheUserHasToBeTeacher $toDeleteTheLessonTheUserHasToBeTeacher,
        ToDeleteTheCourseTheUserHasToBeTeacher $toDeleteTheCourseTheUserHasToBeTeacher,
        CannotDeleteLessonFromOngoingOrEndedCourse $cannotDeleteLessonFromOngoingOrEndedCourse,
        CannotDeleteOngoingOrEndedCourse $cannotDeleteOngoingOrEndedCourse,
        AttemptToLogIntoAnUnconfirmedUserAccount $attemptToLogIntoAnUnconfirmedUserAccount,
        CannotDeleteTeacherWithOngoingCourses $cannotDeleteTeacherWithOngoingCourses,
        CannotDeleteStudentWhichIsSubscribedToOngoingCourses $cannotDeleteStudentWhichIsSubscribedToOngoingCourses,
        SessionFoundByApiTokenButItsClientIdDoesNotMatch $sessionFoundByApiTokenButItsClientIdDoesNotMatch,
        SessionIsLocked $sessionIsLocked,
        IncorrectPasswordHasBeenEntered $incorrectPasswordHasBeenEntered,
        AccountHasBeenLocked $accountHasBeenLocked,
        SecurityCodeHasBeenGenerated $securityCodeHasBeenGenerated,
        IncorrectSecurityCodeHasBeenEntered $incorrectSecurityCodeHasBeenEntered,
        SecurityCodeHasBeenGeneratedAgain $securityCodeHasBeenGeneratedAgain,
        SecurityCodeHasExpired $securityCodeHasExpired,
        UserIsTryingToUseAnotherEmailAddress $userIsTryingToUseAnotherEmailAddress,
        OldApiClientIdIsDifferentThanTheOneInCurrentSession $oldApiClientIdIsDifferentThanTheOneInCurrentSession,
        UserDoesNotHaveAnySecurityCode $userDoesNotHaveAnySecurityCode,
        CannotSubscribeToYourOwnCourse $cannotSubscribeToYourOwnCourse,
        CannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo $cannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo,
        ToUpdateTheLessonTheUserHasToBeTeacher $toUpdateTheLessonTheUserHasToBeTeacher,
        ToUpdateTheCourseTheUserHasToBeTeacher $toUpdateTheCourseTheUserHasToBeTeacher,
        CannotUpdateLessonFromOngoingOrEndedCourse $cannotUpdateLessonFromOngoingOrEndedCourse,
        CannotUpdateOngoingOrEndedCourse $cannotUpdateOngoingOrEndedCourse,
        StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyFirstNameInRequestBody,
        StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyLastNameInRequestBody,
        StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyEmailInRequestBody,
        StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertyNameInRequestBody,
        StringLengthMustNotBeLonger $stringLengthMustNotBeLongerForPropertySubjectInRequestBody,
        NumberSizeMustNotBeHigher $numberSizeMustNotBeHigherForPropertyPriceInRequestBody,
        SelectedTimezoneIsInvalid $selectedTimezoneIsInvalid,
        CannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo $cannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo,
        CannotSendPaymentForTheSameCourseAgain $cannotSendPaymentForTheSameCourseAgain,
        CannotSendPaymentForTheOngoingOrEndedCourse $cannotSendPaymentForTheOngoingOrEndedCourse,
        CannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo $cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo,
        CannotRequestRefundForThePaymentWhichYouDidNotMake $cannotRequestRefundForThePaymentWhichYouDidNotMake,
        CannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest $cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest,
        CannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest $cannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest,
        TryingToGetLessonListForCourseWhichIsNotYours $tryingToGetLessonListForCourseWhichIsNotYours,
        TryingToUploadAvatarImageOfUnsupportedType $tryingToUploadAvatarImageOfUnsupportedType,
        TryingToUploadAvatarImageOfUnsupportedTypeIfNotNull $tryingToUploadAvatarImageOfUnsupportedTypeIfNotNull
    ) {
        $this->performRequestValidation = $performRequestValidation;
        $this->missingMandatoryApiKeyHttpHeader = $missingMandatoryApiKeyHttpHeader;
        $this->missingMandatoryApiClientIdHttpHeader = $missingMandatoryApiClientIdHttpHeader;
        $this->missingMandatoryApiTokenHttpHeader = $missingMandatoryApiTokenHttpHeader;
        $this->missingValueForApiKeyHttpHeader = $missingValueForApiKeyHttpHeader;
        $this->missingValueForApiClientIdHttpHeader = $missingValueForApiClientIdHttpHeader;
        $this->missingValueForApiTokenHttpHeader = $missingValueForApiTokenHttpHeader;
        $this->invalidValueForApiKeyHttpHeader = $invalidValueForApiKeyHttpHeader;
        $this->invalidValueForApiClientIdHttpHeader = $invalidValueForApiClientIdHttpHeader;
        $this->invalidValueForApiTokenHttpHeader = $invalidValueForApiTokenHttpHeader;
        $this->checkIfHttpMethodIsGet = $checkIfHttpMethodIsGet;
        $this->checkIfHttpMethodIsPost = $checkIfHttpMethodIsPost;
        $this->checkIfHttpMethodIsDelete = $checkIfHttpMethodIsDelete;
        $this->checkIfHttpMethodIsPatch = $checkIfHttpMethodIsPatch;
        $this->missingMandatoryIdUrlParameter = $missingMandatoryIdUrlParameter;
        $this->missingMandatoryEmailUrlParameter = $missingMandatoryEmailUrlParameter;
        $this->missingMandatoryTokenUrlParameter = $missingMandatoryTokenUrlParameter;
        $this->missingValueForIdUrlParameter = $missingValueForIdUrlParameter;
        $this->missingValueForEmailUrlParameter = $missingValueForEmailUrlParameter;
        $this->missingValueForTokenUrlParameter = $missingValueForTokenUrlParameter;
        $this->noDataFoundForCourseIdUrlParameter = $noDataFoundForCourseIdUrlParameter;
        $this->noDataFoundForLessonIdUrlParameter = $noDataFoundForLessonIdUrlParameter;
        $this->noDataFoundForEmailUrlParameter = $noDataFoundForEmailUrlParameter;
        $this->noDataFoundForTokenUrlParameter = $noDataFoundForTokenUrlParameter;
        $this->userNotFoundByEmailCredentialsInUrlParameters = $userNotFoundByEmailCredentialsInUrlParameters;
        $this->missingJsonInRequestBody = $missingJsonInRequestBody;
        $this->malformedJsonInRequestBody = $malformedJsonInRequestBody;
        $this->missingMandatoryPropertyIdInRequestBody = $missingMandatoryPropertyIdInRequestBody;
        $this->missingMandatoryPropertyCourseIdInRequestBody = $missingMandatoryPropertyCourseIdInRequestBody;
        $this->missingMandatoryPropertyTeacherIdInRequestBody = $missingMandatoryPropertyTeacherIdInRequestBody;
        $this->missingMandatoryPropertyUserIdInRequestBody = $missingMandatoryPropertyUserIdInRequestBody;
        $this->missingMandatoryPropertyFirstNameInRequestBody = $missingMandatoryPropertyFirstNameInRequestBody;
        $this->missingMandatoryPropertyLastNameInRequestBody = $missingMandatoryPropertyLastNameInRequestBody;
        $this->missingMandatoryPropertyAvatarInRequestBody = $missingMandatoryPropertyAvatarInRequestBody;
        $this->missingMandatoryPropertyEmailInRequestBody = $missingMandatoryPropertyEmailInRequestBody;
        $this->missingMandatoryPropertyPasswordInRequestBody = $missingMandatoryPropertyPasswordInRequestBody;
        $this->missingMandatoryPropertyIsTeacherInRequestBody = $missingMandatoryPropertyIsTeacherInRequestBody;
        $this->missingMandatoryPropertyIsStudentInRequestBody = $missingMandatoryPropertyIsStudentInRequestBody;
        $this->missingMandatoryPropertyTimezoneInRequestBody = $missingMandatoryPropertyTimezoneInRequestBody;
        $this->missingMandatoryPropertyTokenInRequestBody = $missingMandatoryPropertyTokenInRequestBody;
        $this->missingMandatoryPropertyNameInRequestBody = $missingMandatoryPropertyNameInRequestBody;
        $this->missingMandatoryPropertySubjectInRequestBody = $missingMandatoryPropertySubjectInRequestBody;
        $this->missingMandatoryPropertyFromInRequestBody = $missingMandatoryPropertyFromInRequestBody;
        $this->missingMandatoryPropertyToInRequestBody = $missingMandatoryPropertyToInRequestBody;
        $this->missingMandatoryPropertyOldApiClientIdInRequestBody = $missingMandatoryPropertyOldApiClientIdInRequestBody;
        $this->missingMandatoryPropertySecurityCodeInRequestBody = $missingMandatoryPropertySecurityCodeInRequestBody;
        $this->missingMandatoryPropertyIsActiveInRequestBody = $missingMandatoryPropertyIsActiveInRequestBody;
        $this->missingMandatoryPropertyPriceInRequestBody = $missingMandatoryPropertyPriceInRequestBody;
        $this->expectedDifferentDataTypeForPropertyIdInRequestBody = $expectedDifferentDataTypeForPropertyIdInRequestBody;
        $this->expectedDifferentDataTypeForPropertyCourseIdInRequestBody = $expectedDifferentDataTypeForPropertyCourseIdInRequestBody;
        $this->expectedDifferentDataTypeForPropertyTeacherIdInRequestBody = $expectedDifferentDataTypeForPropertyTeacherIdInRequestBody;
        $this->expectedDifferentDataTypeForPropertyUserIdInRequestBody = $expectedDifferentDataTypeForPropertyUserIdInRequestBody;
        $this->expectedDifferentDataTypeForPropertyFirstNameInRequestBody = $expectedDifferentDataTypeForPropertyFirstNameInRequestBody;
        $this->expectedDifferentDataTypeForPropertyLastNameInRequestBody = $expectedDifferentDataTypeForPropertyLastNameInRequestBody;
        $this->expectedDifferentDataTypeForPropertyAvatarInRequestBody = $expectedDifferentDataTypeForPropertyAvatarInRequestBody;
        $this->expectedDifferentDataTypeForNullablePropertyAvatarInRequestBody = $expectedDifferentDataTypeForNullablePropertyAvatarInRequestBody;
        $this->expectedDifferentDataTypeForPropertyEmailInRequestBody = $expectedDifferentDataTypeForPropertyEmailInRequestBody;
        $this->expectedDifferentDataTypeForPropertyPasswordInRequestBody = $expectedDifferentDataTypeForPropertyPasswordInRequestBody;
        $this->expectedDifferentDataTypeForNullablePropertyPasswordInRequestBody = $expectedDifferentDataTypeForNullablePropertyPasswordInRequestBody;
        $this->expectedDifferentDataTypeForPropertyIsTeacherInRequestBody = $expectedDifferentDataTypeForPropertyIsTeacherInRequestBody;
        $this->expectedDifferentDataTypeForPropertyIsStudentInRequestBody = $expectedDifferentDataTypeForPropertyIsStudentInRequestBody;
        $this->expectedDifferentDataTypeForPropertyTimezoneInRequestBody = $expectedDifferentDataTypeForPropertyTimezoneInRequestBody;
        $this->expectedDifferentDataTypeForPropertyTokenInRequestBody = $expectedDifferentDataTypeForPropertyTokenInRequestBody;
        $this->expectedDifferentDataTypeForPropertyNameInRequestBody = $expectedDifferentDataTypeForPropertyNameInRequestBody;
        $this->expectedDifferentDataTypeForPropertySubjectInRequestBody = $expectedDifferentDataTypeForPropertySubjectInRequestBody;
        $this->expectedDifferentDataTypeForPropertyFromInRequestBody = $expectedDifferentDataTypeForPropertyFromInRequestBody;
        $this->expectedDifferentDataTypeForPropertyToInRequestBody = $expectedDifferentDataTypeForPropertyToInRequestBody;
        $this->expectedDifferentDataTypeForPropertyOldApiClientIdInRequestBody = $expectedDifferentDataTypeForPropertyOldApiClientIdInRequestBody;
        $this->expectedDifferentDataTypeForPropertySecurityCodeInRequestBody = $expectedDifferentDataTypeForPropertySecurityCodeInRequestBody;
        $this->expectedDifferentDataTypeForPropertyIsActiveInRequestBody = $expectedDifferentDataTypeForPropertyIsActiveInRequestBody;
        $this->expectedNumberForPropertyPriceInRequestBody = $expectedNumberForPropertyPriceInRequestBody;
        $this->expectedNumberOrNullForPropertyPriceInRequestBody = $expectedNumberOrNullForPropertyPriceInRequestBody;
        $this->expectedNonEmptyStringInIdInRequestBody = $expectedNonEmptyStringInIdInRequestBody;
        $this->expectedNonEmptyStringInCourseIdInRequestBody = $expectedNonEmptyStringInCourseIdInRequestBody;
        $this->expectedNonEmptyStringInTeacherIdInRequestBody = $expectedNonEmptyStringInTeacherIdInRequestBody;
        $this->expectedNonEmptyStringInUserIdInRequestBody = $expectedNonEmptyStringInUserIdInRequestBody;
        $this->expectedNonEmptyStringInFirstNameInRequestBody = $expectedNonEmptyStringInFirstNameInRequestBody;
        $this->expectedNonEmptyStringInLastNameInRequestBody = $expectedNonEmptyStringInLastNameInRequestBody;
        $this->expectedNonEmptyStringInAvatarInRequestBody = $expectedNonEmptyStringInAvatarInRequestBody;
        $this->expectedNonEmptyStringOrNullInAvatarInRequestBody = $expectedNonEmptyStringOrNullInAvatarInRequestBody;
        $this->expectedNonEmptyStringInEmailInRequestBody = $expectedNonEmptyStringInEmailInRequestBody;
        $this->expectedNonEmptyStringInPasswordInRequestBody = $expectedNonEmptyStringInPasswordInRequestBody;
        $this->expectedNonEmptyStringOrNullInPasswordInRequestBody = $expectedNonEmptyStringOrNullInPasswordInRequestBody;
        $this->expectedNonEmptyStringInTimezoneInRequestBody = $expectedNonEmptyStringInTimezoneInRequestBody;
        $this->expectedNonEmptyStringInTokenInRequestBody = $expectedNonEmptyStringInTokenInRequestBody;
        $this->expectedNonEmptyStringInNameInRequestBody = $expectedNonEmptyStringInNameInRequestBody;
        $this->expectedNonEmptyStringInSubjectInRequestBody = $expectedNonEmptyStringInSubjectInRequestBody;
        $this->expectedNonEmptyStringInFromInRequestBody = $expectedNonEmptyStringInFromInRequestBody;
        $this->expectedNonEmptyStringInToInRequestBody = $expectedNonEmptyStringInToInRequestBody;
        $this->expectedNonEmptyStringInOldApiClientIdInRequestBody = $expectedNonEmptyStringInOldApiClientIdInRequestBody;
        $this->expectedNonEmptyStringInSecurityCodeInRequestBody = $expectedNonEmptyStringInSecurityCodeInRequestBody;
        $this->noDataFoundInCoursesForPropertyCourseIdInRequestBody = $noDataFoundInCoursesForPropertyCourseIdInRequestBody;
        $this->noDataFoundInUsersForPropertyUserIdInRequestBody = $noDataFoundInUsersForPropertyUserIdInRequestBody;
        $this->noDataFoundForPropertyCourseIdInRequestBody = $noDataFoundForPropertyCourseIdInRequestBody;
        $this->noDataFoundForPropertyLessonIdInRequestBody = $noDataFoundForPropertyLessonIdInRequestBody;
        $this->noDataFoundForPropertyTeacherIdInRequestBody = $noDataFoundForPropertyTeacherIdInRequestBody;
        $this->noDataFoundForPropertyEmailInRequestBody = $noDataFoundForPropertyEmailInRequestBody;
        $this->noDataFoundForPropertyUserIdInRequestBody = $noDataFoundForPropertyUserIdInRequestBody;
        $this->userNotFoundByCredentials = $userNotFoundByCredentials;
        $this->userNotFoundByEmailCredentials = $userNotFoundByEmailCredentials;
        $this->numericValueInPriceMustBeGreaterThanZeroInRequestBody = $numericValueInPriceMustBeGreaterThanZeroInRequestBody;
        $this->numericValueInPriceMustBeGreaterThanZeroOrNullInRequestBody = $numericValueInPriceMustBeGreaterThanZeroOrNullInRequestBody;
        $this->malformedEmailInUrlParameter = $malformedEmailInUrlParameter;
        $this->malformedEmailInRequestBody = $malformedEmailInRequestBody;
        $this->malformedDateTimeInPropertyFromInRequestBody = $malformedDateTimeInPropertyFromInRequestBody;
        $this->malformedDateTimeInPropertyToInRequestBody = $malformedDateTimeInPropertyToInRequestBody;
        $this->tokenExpired = $tokenExpired;
        $this->tokenInUrlParameterExpired = $tokenInUrlParameterExpired;
        $this->malformedUuidForPropertyIdInRequestBody = $malformedUuidForPropertyIdInRequestBody;
        $this->malformedUuidForPropertyCourseIdInRequestBody = $malformedUuidForPropertyCourseIdInRequestBody;
        $this->malformedUuidForPropertyTeacherIdInRequestBody = $malformedUuidForPropertyTeacherIdInRequestBody;
        $this->malformedUuidInIdUrlParameter = $malformedUuidInIdUrlParameter;
        $this->givenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody = $givenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody;
        $this->givenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody = $givenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody;
        $this->dateTimeInFromCannotBeGreaterOrEqualToDateTimeInTo = $dateTimeInFromCannotBeGreaterOrEqualToDateTimeInTo;
        $this->selectedUserIsNotTeacher = $selectedUserIsNotTeacher;
        $this->userIsNotTeacherSoPriceMustNotBeSet = $userIsNotTeacherSoPriceMustNotBeSet;
        $this->userIsTeacherSoPriceMustBeSet = $userIsTeacherSoPriceMustBeSet;
        $this->cannotSubscribeToInactiveCourse = $cannotSubscribeToInactiveCourse;
        $this->cannotSubscribeToOngoingOrEndedCourse = $cannotSubscribeToOngoingOrEndedCourse;
        $this->cannotUnsubscribeFromOngoingOrEndedCourse = $cannotUnsubscribeFromOngoingOrEndedCourse;
        $this->toAcceptThisRequestTheUserHasToBeTeacher = $toAcceptThisRequestTheUserHasToBeTeacher;
        $this->toDeleteTheLessonTheUserHasToBeTeacher = $toDeleteTheLessonTheUserHasToBeTeacher;
        $this->toDeleteTheCourseTheUserHasToBeTeacher = $toDeleteTheCourseTheUserHasToBeTeacher;
        $this->cannotDeleteLessonFromOngoingOrEndedCourse = $cannotDeleteLessonFromOngoingOrEndedCourse;
        $this->cannotDeleteOngoingOrEndedCourse = $cannotDeleteOngoingOrEndedCourse;
        $this->attemptToLogIntoAnUnconfirmedUserAccount = $attemptToLogIntoAnUnconfirmedUserAccount;
        $this->cannotDeleteTeacherWithOngoingCourses = $cannotDeleteTeacherWithOngoingCourses;
        $this->cannotDeleteStudentWhichIsSubscribedToOngoingCourses = $cannotDeleteStudentWhichIsSubscribedToOngoingCourses;
        $this->sessionFoundByApiTokenButItsClientIdDoesNotMatch = $sessionFoundByApiTokenButItsClientIdDoesNotMatch;
        $this->sessionIsLocked = $sessionIsLocked;
        $this->incorrectPasswordHasBeenEntered = $incorrectPasswordHasBeenEntered;
        $this->accountHasBeenLocked = $accountHasBeenLocked;
        $this->securityCodeHasBeenGenerated = $securityCodeHasBeenGenerated;
        $this->incorrectSecurityCodeHasBeenEntered = $incorrectSecurityCodeHasBeenEntered;
        $this->securityCodeHasBeenGeneratedAgain = $securityCodeHasBeenGeneratedAgain;
        $this->securityCodeHasExpired = $securityCodeHasExpired;
        $this->userIsTryingToUseAnotherEmailAddress = $userIsTryingToUseAnotherEmailAddress;
        $this->oldApiClientIdIsDifferentThanTheOneInCurrentSession = $oldApiClientIdIsDifferentThanTheOneInCurrentSession;
        $this->userDoesNotHaveAnySecurityCode = $userDoesNotHaveAnySecurityCode;
        $this->cannotSubscribeToYourOwnCourse = $cannotSubscribeToYourOwnCourse;
        $this->cannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo = $cannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo;
        $this->toUpdateTheLessonTheUserHasToBeTeacher = $toUpdateTheLessonTheUserHasToBeTeacher;
        $this->toUpdateTheCourseTheUserHasToBeTeacher = $toUpdateTheCourseTheUserHasToBeTeacher;
        $this->cannotUpdateLessonFromOngoingOrEndedCourse = $cannotUpdateLessonFromOngoingOrEndedCourse;
        $this->cannotUpdateOngoingOrEndedCourse = $cannotUpdateOngoingOrEndedCourse;
        $this->stringLengthMustNotBeLongerForPropertyFirstNameInRequestBody = $stringLengthMustNotBeLongerForPropertyFirstNameInRequestBody;
        $this->stringLengthMustNotBeLongerForPropertyLastNameInRequestBody = $stringLengthMustNotBeLongerForPropertyLastNameInRequestBody;
        $this->stringLengthMustNotBeLongerForPropertyEmailInRequestBody = $stringLengthMustNotBeLongerForPropertyEmailInRequestBody;
        $this->stringLengthMustNotBeLongerForPropertyNameInRequestBody = $stringLengthMustNotBeLongerForPropertyNameInRequestBody;
        $this->stringLengthMustNotBeLongerForPropertySubjectInRequestBody = $stringLengthMustNotBeLongerForPropertySubjectInRequestBody;
        $this->numberSizeMustNotBeHigherForPropertyPriceInRequestBody = $numberSizeMustNotBeHigherForPropertyPriceInRequestBody;
        $this->selectedTimezoneIsInvalid = $selectedTimezoneIsInvalid;
        $this->cannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo = $cannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo;
        $this->cannotSendPaymentForTheSameCourseAgain = $cannotSendPaymentForTheSameCourseAgain;
        $this->cannotSendPaymentForTheOngoingOrEndedCourse = $cannotSendPaymentForTheOngoingOrEndedCourse;
        $this->cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo = $cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo;
        $this->cannotRequestRefundForThePaymentWhichYouDidNotMake = $cannotRequestRefundForThePaymentWhichYouDidNotMake;
        $this->cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest = $cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest;
        $this->cannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest = $cannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest;
        $this->tryingToGetLessonListForCourseWhichIsNotYours = $tryingToGetLessonListForCourseWhichIsNotYours;
        $this->tryingToUploadAvatarImageOfUnsupportedType = $tryingToUploadAvatarImageOfUnsupportedType;
        $this->tryingToUploadAvatarImageOfUnsupportedTypeIfNotNull = $tryingToUploadAvatarImageOfUnsupportedTypeIfNotNull;
    }

    public function performRequestValidation(): bool
    {
        return $this->performRequestValidation;
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiKeyHttpHeaderIsMissing(HeaderBag $headers): void
    {
        try {
            $this->missingMandatoryApiKeyHttpHeader->checkIfHttpHeaderIsMissing($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiClientIdHttpHeaderIsMissing(HeaderBag $headers): void
    {
        try {
            $this->missingMandatoryApiClientIdHttpHeader->checkIfHttpHeaderIsMissing($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiTokenHttpHeaderIsMissing(HeaderBag $headers): void
    {
        try {
            $this->missingMandatoryApiTokenHttpHeader->checkIfHttpHeaderIsMissing($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiKeyHttpHeaderIsEmpty(HeaderBag $headers): void
    {
        try {
            $this->missingValueForApiKeyHttpHeader->checkIfHttpHeaderIsEmpty($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiClientIdHttpHeaderIsEmpty(HeaderBag $headers): void
    {
        try {
            $this->missingValueForApiClientIdHttpHeader->checkIfHttpHeaderIsEmpty($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiTokenHttpHeaderIsEmpty(HeaderBag $headers): void
    {
        try {
            $this->missingValueForApiTokenHttpHeader->checkIfHttpHeaderIsEmpty($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiKeyHttpHeaderIsInvalid(HeaderBag $headers): void
    {
        try {
            $this->invalidValueForApiKeyHttpHeader->checkIfApiKeyIsInvalid($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiClientIdHttpHeaderIsInvalid(HeaderBag $headers): void
    {
        try {
            $this->invalidValueForApiClientIdHttpHeader->checkIfApiClientIdIsInvalid($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfApiTokenHttpHeaderIsInvalid(HeaderBag $headers): void
    {
        try {
            $this->invalidValueForApiTokenHttpHeader->checkIfApiTokenIsInvalid($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfHttpMethodIsGet(string $givenMethod): void
    {
        try {
            $this->checkIfHttpMethodIsGet->checkIfHttpMethodIsCorrect($givenMethod);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfHttpMethodIsPost(string $givenMethod): void
    {
        try {
            $this->checkIfHttpMethodIsPost->checkIfHttpMethodIsCorrect($givenMethod);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfHttpMethodIsDelete(string $givenMethod): void
    {
        try {
            $this->checkIfHttpMethodIsDelete->checkIfHttpMethodIsCorrect($givenMethod);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfHttpMethodIsPatch(string $givenMethod): void
    {
        try {
            $this->checkIfHttpMethodIsPatch->checkIfHttpMethodIsCorrect($givenMethod);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfIdUrlParameterIsMissing(ParameterBag $parameters): void
    {
        try {
            $this->missingMandatoryIdUrlParameter->checkIfUrlParameterIsMissing($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfEmailUrlParameterIsMissing(ParameterBag $parameters): void
    {
        try {
            $this->missingMandatoryEmailUrlParameter->checkIfUrlParameterIsMissing($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTokenUrlParameterIsMissing(ParameterBag $parameters): void
    {
        try {
            $this->missingMandatoryTokenUrlParameter->checkIfUrlParameterIsMissing($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfIdUrlParameterIsEmpty(ParameterBag $parameters): void
    {
        try {
            $this->missingValueForIdUrlParameter->checkIfUrlParameterIsEmpty($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfEmailUrlParameterIsEmpty(ParameterBag $parameters): void
    {
        try {
            $this->missingValueForEmailUrlParameter->checkIfUrlParameterIsEmpty($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTokenUrlParameterIsEmpty(ParameterBag $parameters): void
    {
        try {
            $this->missingValueForTokenUrlParameter->checkIfUrlParameterIsEmpty($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForUrlParameterCourseIdWereFound(ParameterBag $parameters): void
    {
        try {
            $this->noDataFoundForCourseIdUrlParameter->checkIfAnyDataForUrlParameterCourseIdWereFound($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForUrlParameterLessonIdWereFound(ParameterBag $parameters): void
    {
        try {
            $this->noDataFoundForLessonIdUrlParameter->checkIfAnyDataForUrlParameterLessonIdWereFound($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForUrlParameterEmailWereFound(ParameterBag $parameters): void
    {
        try {
            $this->noDataFoundForEmailUrlParameter->checkIfAnyDataForUrlParameterEmailWereFound($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForUrlParameterTokenWereFound(ParameterBag $parameters): void
    {
        try {
            $this->noDataFoundForTokenUrlParameter->checkIfAnyDataForUrlParameterTokenWereFound($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserEmailCredentialsInUrlParametersAreCorrect(ParameterBag $parameters): void
    {
        try {
            $this->userNotFoundByEmailCredentialsInUrlParameters->checkIfUserEmailCredentialsInUrlParametersAreCorrect($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfThereIsMissingJsonInRequestBody(string $requestBody): void
    {
        try {
            $this->missingJsonInRequestBody->checkIfThereIsMissingJsonInRequestBody($requestBody);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfThereIsMalformedJsonInRequestBody(?array $data): void
    {
        try {
            $this->malformedJsonInRequestBody->checkIfThereIsMalformedJsonInRequestBody($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIdIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyIdInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyCourseIdIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyCourseIdInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTeacherIdIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyTeacherIdInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyUserIdIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyUserIdInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyFirstNameIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyFirstNameInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyLastNameIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyLastNameInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyAvatarIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyAvatarInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyEmailIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyEmailInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPasswordIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyPasswordInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIsTeacherIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyIsTeacherInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIsStudentIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyIsStudentInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTimezoneIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyTimezoneInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTokenIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyTokenInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyNameIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyNameInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertySubjectIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertySubjectInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyFromIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyFromInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyToIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyToInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyOldApiClientIdIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyOldApiClientIdInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertySecurityCodeIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertySecurityCodeInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIsActiveIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyIsActiveInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPriceIsMissingInRequestBody(array $data): void
    {
        try {
            $this->missingMandatoryPropertyPriceInRequestBody->checkIfPropertyIsMissing($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIdIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyIdInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyCourseIdIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyCourseIdInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTeacherIdIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyTeacherIdInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyUserIdIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyUserIdInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyFirstNameIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyFirstNameInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyLastNameIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyLastNameInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyAvatarIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyAvatarInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfNullablePropertyAvatarIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForNullablePropertyAvatarInRequestBody->checkIfNullablePropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyEmailIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyEmailInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPasswordIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyPasswordInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfNullablePropertyPasswordIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForNullablePropertyPasswordInRequestBody->checkIfNullablePropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIsTeacherIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyIsTeacherInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIsStudentIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyIsStudentInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTimezoneIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyTimezoneInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTokenIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyTokenInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyNameIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyNameInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertySubjectIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertySubjectInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyFromIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyFromInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyToIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyToInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyOldApiClientIdIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyOldApiClientIdInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertySecurityCodeIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertySecurityCodeInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIsActiveIsOfCorrectDataTypeInRequestBody(array $data): void
    {
        try {
            $this->expectedDifferentDataTypeForPropertyIsActiveInRequestBody->checkIfPropertyIsOfCorrectDataType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPriceIsNumberInRequestBody(array $data): void
    {
        try {
            $this->expectedNumberForPropertyPriceInRequestBody->checkIfPropertyPriceIsNumber($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPriceIsNumberOrNullInRequestBody(array $data): void
    {
        try {
            $this->expectedNumberOrNullForPropertyPriceInRequestBody->checkIfPropertyPriceIsNumberOrNull($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyIdIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInIdInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyCourseIdIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInCourseIdInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTeacherIdIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInTeacherIdInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyUserIdIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInUserIdInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyFirstNameIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInFirstNameInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyLastNameIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInLastNameInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyAvatarIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInAvatarInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyAvatarIsNonEmptyStringOrNullInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringOrNullInAvatarInRequestBody->checkIfPropertyIsNonEmptyStringOrNull($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyEmailIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInEmailInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPasswordIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInPasswordInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPasswordIsNonEmptyStringOrNullInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringOrNullInPasswordInRequestBody->checkIfPropertyIsNonEmptyStringOrNull($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTimezoneIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInTimezoneInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyTokenIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInTokenInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyNameIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInNameInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertySubjectIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInSubjectInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyFromIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInFromInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyToIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInToInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyOldApiClientIdIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInOldApiClientIdInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertySecurityCodeIsNonEmptyStringInRequestBody(array $data): void
    {
        try {
            $this->expectedNonEmptyStringInSecurityCodeInRequestBody->checkIfPropertyIsNonEmptyString($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataWereFoundInCoursesForPropertyCourseId(array $data): void
    {
        try {
            $this->noDataFoundInCoursesForPropertyCourseIdInRequestBody->checkIfAnyDataWereFoundInCoursesForPropertyCourseId($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataWereFoundInUsersForPropertyUserId(array $data): void
    {
        try {
            $this->noDataFoundInUsersForPropertyUserIdInRequestBody->checkIfAnyDataWereFoundInUsersForPropertyUserId($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForPropertyCourseIdWereFound(array $data): void
    {
        try {
            $this->noDataFoundForPropertyCourseIdInRequestBody->checkIfAnyDataForPropertyCourseIdWereFound($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForPropertyLessonIdWereFound(array $data): void
    {
        try {
            $this->noDataFoundForPropertyLessonIdInRequestBody->checkIfAnyDataForPropertyLessonIdWereFound($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForPropertyTeacherIdWereFound(array $data): void
    {
        try {
            $this->noDataFoundForPropertyTeacherIdInRequestBody->checkIfAnyDataForPropertyTeacherIdWereFound($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForPropertyEmailWereFound(array $data): void
    {
        try {
            $this->noDataFoundForPropertyEmailInRequestBody->checkIfAnyDataForPropertyEmailWereFound($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfAnyDataForPropertyUserIdWereFound(array $data): void
    {
        try {
            $this->noDataFoundForPropertyUserIdInRequestBody->checkIfAnyDataForPropertyUserIdWereFound($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserCredentialsAreCorrect(array $data): void
    {
        try {
            $this->userNotFoundByCredentials->checkIfUserCredentialsAreCorrect($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserEmailCredentialsAreCorrect(array $data): void
    {
        try {
            $this->userNotFoundByEmailCredentials->checkIfUserEmailCredentialsAreCorrect($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPriceIsGreaterThanZeroInRequestBody(array $data): void
    {
        try {
            $this->numericValueInPriceMustBeGreaterThanZeroInRequestBody->checkIfPropertyNumericValueIsGreater($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyPriceIsGreaterThanZeroOrNullInRequestBody(array $data): void
    {
        try {
            $this->numericValueInPriceMustBeGreaterThanZeroOrNullInRequestBody->checkIfPropertyNumericValueIsGreaterOrNull($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfEmailUrlParameterIsMalformed(ParameterBag $parameters): void
    {
        try {
            $this->malformedEmailInUrlParameter->checkIfEmailUrlParameterIsMalformed($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfPropertyEmailIsMalformedInRequestBody(array $data): void
    {
        try {
            $this->malformedEmailInRequestBody->checkIfPropertyEmailIsMalformed($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfDateTimeIsMalformedInPropertyFromInRequestBody(array $data): void
    {
        try {
            $this->malformedDateTimeInPropertyFromInRequestBody->checkIfDateTimeIsMalformed($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfDateTimeIsMalformedInPropertyToInRequestBody(array $data): void
    {
        try {
            $this->malformedDateTimeInPropertyToInRequestBody->checkIfDateTimeIsMalformed($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws TokenExpiredException
     */
    public function checkIfTokenHasExpired(array $data): void
    {
        try {
            $this->tokenExpired->checkIfTokenHasExpired($data);
        } catch (TokenExpiredException $e) {
            throw $e;
        }
    }

    /**
     * @throws TokenExpiredException
     */
    public function checkIfTokenInUrlParameterHasExpired(ParameterBag $parameters): void
    {
        try {
            $this->tokenInUrlParameterExpired->checkIfTokenInUrlParameterHasExpired($parameters);
        } catch (TokenExpiredException $e) {
            throw $e;
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUuidForPropertyIdIsMalformedInRequestBody(array $data): void
    {
        try {
            $this->malformedUuidForPropertyIdInRequestBody->checkIfUuidIsMalformed($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUuidForPropertyCourseIdIsMalformedInRequestBody(array $data): void
    {
        try {
            $this->malformedUuidForPropertyCourseIdInRequestBody->checkIfUuidIsMalformed($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUuidForPropertyTeacherIdIsMalformedInRequestBody(array $data): void
    {
        try {
            $this->malformedUuidForPropertyTeacherIdInRequestBody->checkIfUuidIsMalformed($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUuidIsMalformedInIdUrlParameter(ParameterBag $parameters): void
    {
        try {
            $this->malformedUuidInIdUrlParameter->checkIfUuidIsMalformed($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfGivenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody(array $data): void
    {
        try {
            $this->givenDateTimeDoesNotMakeAnySenseInPropertyFromInRequestBody->checkIfGivenDateTimeMakesAnySense($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfGivenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody(array $data): void
    {
        try {
            $this->givenDateTimeDoesNotMakeAnySenseInPropertyToInRequestBody->checkIfGivenDateTimeMakesAnySense($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfDateTimeInFromIsGreaterOrEqualToDateTimeInTo(array $data): void
    {
        try {
            $this->dateTimeInFromCannotBeGreaterOrEqualToDateTimeInTo->checkIfDateTimeInOneIsGreaterOrEqualToDateTimeInTwo($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfSelectedUserIsTeacher(array $data): void
    {
        try {
            $this->selectedUserIsNotTeacher->checkIfSelectedUserIsTeacher($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserIsNotTeacherSoPriceIsNotSet(array $data): void
    {
        try {
            $this->userIsNotTeacherSoPriceMustNotBeSet->checkIfUserIsNotTeacherSoPriceIsNotSet($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserIsTeacherSoPriceIsSet(array $data): void
    {
        try {
            $this->userIsTeacherSoPriceMustBeSet->checkIfUserIsTeacherSoPriceIsSet($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotSubscribeToInactiveCourse(array $data): void
    {
        try {
            $this->cannotSubscribeToInactiveCourse->checkIfCannotSubscribeToInactiveCourse($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotSubscribeToOngoingOrEndedCourse(array $data): void
    {
        try {
            $this->cannotSubscribeToOngoingOrEndedCourse->checkIfCannotSubscribeToOngoingOrEndedCourse($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotUnsubscribeFromOngoingOrEndedCourse(array $data): void
    {
        try {
            $this->cannotUnsubscribeFromOngoingOrEndedCourse->checkIfCannotUnsubscribeFromOngoingOrEndedCourse($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTheUserIsTeacherToAcceptThisRequest(HeaderBag $headers): void
    {
        try {
            $this->toAcceptThisRequestTheUserHasToBeTeacher->checkIfTheUserIsTeacherToAcceptThisRequest($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTheUserIsTeacherToDeleteTheLesson(HeaderBag $headers, array $data): void
    {
        try {
            $this->toDeleteTheLessonTheUserHasToBeTeacher->checkIfTheUserIsTeacherToDeleteTheLesson($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTheUserIsTeacherToDeleteTheCourse(HeaderBag $headers, array $data): void
    {
        try {
            $this->toDeleteTheCourseTheUserHasToBeTeacher->checkIfTheUserIsTeacherToDeleteTheCourse($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotDeleteLessonFromOngoingOrEndedCourse(array $data): void
    {
        try {
            $this->cannotDeleteLessonFromOngoingOrEndedCourse->checkIfCannotDeleteLessonFromOngoingOrEndedCourse($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotDeleteOngoingOrEndedCourse(array $data): void
    {
        try {
            $this->cannotDeleteOngoingOrEndedCourse->checkIfCannotDeleteOngoingOrEndedCourse($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserIsAttemptingToLogIntoAnUnconfirmedAccount(array $data): void
    {
        try {
            $this->attemptToLogIntoAnUnconfirmedUserAccount->checkIfUserIsAttemptingToLogIntoAnUnconfirmedAccount($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotDeleteTeacherWithOngoingCourses(array $data): void
    {
        try {
            $this->cannotDeleteTeacherWithOngoingCourses->checkIfCannotDeleteTeacherWithOngoingCourses($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses(array $data): void
    {
        try {
            $this->cannotDeleteStudentWhichIsSubscribedToOngoingCourses->checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws SessionHasNotMatchingClientIdException
     */
    public function checkIfSessionFoundByApiTokenButItsClientIdDoesNotMatch(HeaderBag $headers): void
    {
        try {
            $this->sessionFoundByApiTokenButItsClientIdDoesNotMatch->checkIfSessionFoundByApiTokenButItsClientIdDoesNotMatch($headers);
        } catch (SessionHasNotMatchingClientIdException $e) {
            throw $e;
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfSessionIsLocked(HeaderBag $headers): void
    {
        try {
            $this->sessionIsLocked->checkIfSessionIsLocked($headers);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws AuthenticationFailureException
     */
    public function checkIfIncorrectPasswordHasBeenEntered(array $data): void
    {
        try {
            $this->incorrectPasswordHasBeenEntered->checkIfIncorrectPasswordHasBeenEntered($data);
        } catch (AuthenticationFailureException $e) {
            throw $e;
        }
    }

    /**
     * @throws LockAccountException
     * @throws RequestValidationException
     */
    public function checkIfAccountHasBeenLocked(array $data): void
    {
        try {
            $this->accountHasBeenLocked->checkIfAccountHasBeenLocked($data);
        } catch (LockAccountException $e) {
            throw $e;
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws SecurityCodeHasToBeGeneratedException
     */
    public function checkIfSecurityCodeHasToBeGenerated(array $data): void
    {
        try {
            $this->securityCodeHasBeenGenerated->checkIfSecurityCodeHasToBeGenerated($data);
        } catch (SecurityCodeHasToBeGeneratedException $e) {
            throw $e;
        }
    }

    /**
     * @throws SecurityCodeConfirmationFailureException
     */
    public function checkIfIncorrectSecurityCodeHasBeenEntered(array $data): void
    {
        try {
            $this->incorrectSecurityCodeHasBeenEntered->checkIfIncorrectSecurityCodeHasBeenEntered($data);
        } catch (SecurityCodeConfirmationFailureException $e) {
            throw $e;
        }
    }

    /**
     * @throws SecurityCodeHasToBeGeneratedAgainException
     */
    public function checkIfSecurityCodeHasToBeGeneratedAgain(array $data): void
    {
        try {
            $this->securityCodeHasBeenGeneratedAgain->checkIfSecurityCodeHasToBeGeneratedAgain($data);
        } catch (SecurityCodeHasToBeGeneratedAgainException $e) {
            throw $e;
        }
    }

    /**
     * @throws SecurityCodeExpiredException
     */
    public function checkIfSecurityCodeHasExpired(array $data): void
    {
        try {
            $this->securityCodeHasExpired->checkIfSecurityCodeHasExpired($data);
        } catch (SecurityCodeExpiredException $e) {
            throw $e;
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserIsTryingToUseAnotherEmailAddress(HeaderBag $headers, array $data): void
    {
        try {
            $this->userIsTryingToUseAnotherEmailAddress->checkIfUserIsTryingToUseAnotherEmailAddress($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfOldApiClientIdIsDifferentThanTheOneInCurrentSession(HeaderBag $headers, array $data): void
    {
        try {
            $this->oldApiClientIdIsDifferentThanTheOneInCurrentSession->checkIfOldApiClientIdIsDifferentThanTheOneInCurrentSession($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfUserDoesHaveAnySecurityCode(array $data): void
    {
        try {
            $this->userDoesNotHaveAnySecurityCode->checkIfUserDoesHaveAnySecurityCode($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotSubscribeToYourOwnCourse(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotSubscribeToYourOwnCourse->checkIfCannotSubscribeToYourOwnCourse($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo->checkIfCannotUnsubscribeFromCourseToWhichYouAreNotSubscribedTo($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTheUserIsTeacherToUpdateTheLesson(HeaderBag $headers, ParameterBag $parameters): void
    {
        try {
            $this->toUpdateTheLessonTheUserHasToBeTeacher->checkIfTheUserIsTeacherToUpdateTheLesson($headers, $parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTheUserIsTeacherToUpdateTheCourse(HeaderBag $headers, ParameterBag $parameters): void
    {
        try {
            $this->toUpdateTheCourseTheUserHasToBeTeacher->checkIfTheUserIsTeacherToUpdateTheCourse($headers, $parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotUpdateLessonFromOngoingOrEndedCourse(ParameterBag $parameters): void
    {
        try {
            $this->cannotUpdateLessonFromOngoingOrEndedCourse->checkIfCannotUpdateLessonFromOngoingOrEndedCourse($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotUpdateOngoingOrEndedCourse(ParameterBag $parameters): void
    {
        try {
            $this->cannotUpdateOngoingOrEndedCourse->checkIfCannotUpdateOngoingOrEndedCourse($parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfStringLengthIsNotLongerForPropertyFirstNameInRequestBody(array $data): void
    {
        try {
            $this->stringLengthMustNotBeLongerForPropertyFirstNameInRequestBody->checkIfStringLengthIsLonger($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfStringLengthIsNotLongerForPropertyLastNameInRequestBody(array $data): void
    {
        try {
            $this->stringLengthMustNotBeLongerForPropertyLastNameInRequestBody->checkIfStringLengthIsLonger($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfStringLengthIsNotLongerForPropertyEmailInRequestBody(array $data): void
    {
        try {
            $this->stringLengthMustNotBeLongerForPropertyEmailInRequestBody->checkIfStringLengthIsLonger($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfStringLengthIsNotLongerForPropertyNameInRequestBody(array $data): void
    {
        try {
            $this->stringLengthMustNotBeLongerForPropertyNameInRequestBody->checkIfStringLengthIsLonger($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfStringLengthIsNotLongerForPropertySubjectInRequestBody(array $data): void
    {
        try {
            $this->stringLengthMustNotBeLongerForPropertySubjectInRequestBody->checkIfStringLengthIsLonger($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfNumberSizeIsNotHigherForPropertyPriceInRequestBody(array $data): void
    {
        try {
            $this->numberSizeMustNotBeHigherForPropertyPriceInRequestBody->checkIfNumberSizeIsHigher($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfSelectedTimezoneIsInvalid(array $data): void
    {
        try {
            $this->selectedTimezoneIsInvalid->checkIfSelectedTimezoneIsInvalid($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo->checkIfCannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotSendPaymentForTheSameCourseAgain(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotSendPaymentForTheSameCourseAgain->checkIfCannotSendPaymentForTheSameCourseAgain($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotSendPaymentForTheOngoingOrEndedCourse(array $data): void
    {
        try {
            $this->cannotSendPaymentForTheOngoingOrEndedCourse->checkIfCannotSendPaymentForTheOngoingOrEndedCourse($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo->checkIfCannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotRequestRefundForThePaymentWhichYouDidNotMake(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotRequestRefundForThePaymentWhichYouDidNotMake->checkIfCannotRequestRefundForThePaymentWhichYouDidNotMake($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest->checkIfCannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfCannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest(HeaderBag $headers, array $data): void
    {
        try {
            $this->cannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest->checkIfCannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest($headers, $data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTryingToGetLessonListForCourseWhichIsNotYours(HeaderBag $headers, ParameterBag $parameters): void
    {
        try {
            $this->tryingToGetLessonListForCourseWhichIsNotYours->checkIfTryingToGetLessonListForCourseWhichIsNotYours($headers, $parameters);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTryingToUploadAvatarImageOfUnsupportedType(array $data): void
    {
        try {
            $this->tryingToUploadAvatarImageOfUnsupportedType->checkIfTryingToUploadAvatarImageOfUnsupportedType($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }

    /**
     * @throws RequestValidationException
     */
    public function checkIfTryingToUploadAvatarImageOfUnsupportedTypeIfNotNull(array $data): void
    {
        try {
            $this->tryingToUploadAvatarImageOfUnsupportedTypeIfNotNull->checkIfTryingToUploadAvatarImageOfUnsupportedTypeIfNotNull($data);
        } catch (ValidationException $e) {
            throw new RequestValidationException($e->getData(), $e->getMessage());
        }
    }
}
