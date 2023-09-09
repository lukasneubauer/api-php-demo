<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\Lesson;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;

class CannotUnsubscribeFromOngoingOrEndedCourse
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotUnsubscribeFromOngoingOrEndedCourse(array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        $lessons = $course->getLessons();
        /** @var Lesson $firstLesson */
        $firstLesson = $lessons[0];
        $courseStartTimestamp = $firstLesson->getFrom()->getTimestamp();
        $nowTimestamp = \time();
        if ($nowTimestamp >= $courseStartTimestamp) {
            $error = Error::cannotUnsubscribeFromOngoingOrEndedCourse();
            $message = \sprintf(Emsg::CANNOT_UNSUBSCRIBE_FROM_ONGOING_OR_ENDED_COURSE);
            throw new ValidationException($error, $message);
        }
    }
}
