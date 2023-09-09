<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\Lesson;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;

class CannotSendPaymentForTheOngoingOrEndedCourse
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotSendPaymentForTheOngoingOrEndedCourse(array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        $lessons = $course->getLessons();
        /** @var Lesson $firstLesson */
        $firstLesson = $lessons[0];
        $courseStartTimestamp = $firstLesson->getFrom()->getTimestamp();
        $nowTimestamp = \time();
        if ($nowTimestamp >= $courseStartTimestamp) {
            $error = Error::cannotSendPaymentForTheOngoingOrEndedCourse();
            $message = Emsg::CANNOT_SEND_PAYMENT_FOR_THE_ONGOING_OR_ENDED_COURSE;
            throw new ValidationException($error, $message);
        }
    }
}
