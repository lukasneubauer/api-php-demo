<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\Course;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class CannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo(HeaderBag $headers, array $data): void
    {
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));
        $user = $session->getUser();
        $courses = $user->getStudentCourses();

        $courseIds = [];

        /** @var Course $course */
        foreach ($courses as $course) {
            $courseIds[] = $course->getId();
        }

        if (\in_array($data['courseId'], $courseIds, true) === false) {
            $error = Error::cannotRequestRefundForTheCourseToWhichYouAreNotSubscribedTo();
            $message = \sprintf(Emsg::CANNOT_REQUEST_REFUND_FOR_THE_COURSE_TO_WHICH_YOU_ARE_NOT_SUBSCRIBED_TO);
            throw new ValidationException($error, $message);
        }
    }
}
