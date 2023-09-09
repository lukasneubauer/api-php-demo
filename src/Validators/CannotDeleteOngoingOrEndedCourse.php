<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\Lesson;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;

class CannotDeleteOngoingOrEndedCourse
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotDeleteOngoingOrEndedCourse(array $data): void
    {
        $course = $this->courseRepository->getById($data['id']);
        $courseLessons = $course->getLessons();
        if ($courseLessons->count() === 0) {
            return;
        }

        /** @var Lesson $firstCourseLesson */
        $firstCourseLesson = $courseLessons[0];
        $courseStartTimestamp = $firstCourseLesson->getFrom()->getTimestamp();
        $isInThePast = \time() >= $courseStartTimestamp;
        $hasStudents = $course->getStudents()->count() > 0;
        if ($course->isActive() && $hasStudents && $isInThePast) {
            $error = Error::cannotDeleteOngoingOrEndedCourse();
            $message = \sprintf(Emsg::CANNOT_DELETE_ONGOING_OR_ENDED_COURSE);
            throw new ValidationException($error, $message);
        }
    }
}
