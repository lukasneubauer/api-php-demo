<?php

declare(strict_types=1);

namespace App\Validators;

use App\Entities\Lesson;
use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\LessonRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class CannotUpdateLessonFromOngoingOrEndedCourse
{
    private LessonRepository $lessonRepository;

    public function __construct(LessonRepository $lessonRepository)
    {
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotUpdateLessonFromOngoingOrEndedCourse(ParameterBag $parameters): void
    {
        $lesson = $this->lessonRepository->getById($parameters->get('id'));
        $course = $lesson->getCourse();
        $courseLessons = $course->getLessons();
        /** @var Lesson $firstCourseLesson */
        $firstCourseLesson = $courseLessons[0];
        $courseStartTimestamp = $firstCourseLesson->getFrom()->getTimestamp();
        $isInThePast = \time() >= $courseStartTimestamp;
        $hasStudents = $course->getStudents()->count() > 0;
        if ($course->isActive() && $hasStudents && $isInThePast) {
            $error = Error::cannotUpdateLessonFromOngoingOrEndedCourse();
            $message = \sprintf(Emsg::CANNOT_UPDATE_LESSON_FROM_ONGOING_OR_ENDED_COURSE);
            throw new ValidationException($error, $message);
        }
    }
}
