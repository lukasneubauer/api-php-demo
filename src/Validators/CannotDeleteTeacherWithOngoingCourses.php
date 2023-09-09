<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class CannotDeleteTeacherWithOngoingCourses
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotDeleteTeacherWithOngoingCourses(array $data): void
    {
        $user = $this->userRepository->getById($data['id']);
        $courses = $user->getTeacherCourses();

        if ($user->isTeacher() && \count($courses) > 0) {
            foreach ($courses as $course) {
                $hasStudents = \count($course->getStudents()) > 0;

                $courseIsOngoing = false;
                $lessons = $course->getLessons();
                if (\count($lessons) > 0) {
                    $firstLesson = $lessons->first();
                    $courseStart = $firstLesson->getFrom();
                    $courseStartTs = $courseStart->getTimestamp();

                    $lastLesson = $lessons->last();
                    $courseEnd = $lastLesson->getTo();
                    $courseEndTs = $courseEnd->getTimestamp();

                    $nowTs = \time();
                    $courseIsOngoing = $courseStartTs <= $nowTs && $courseEndTs >= $nowTs;
                }

                if ($course->isActive() && $hasStudents && $courseIsOngoing) {
                    $error = Error::cannotDeleteTeacherWithOngoingCourses();
                    $message = \sprintf(Emsg::CANNOT_DELETE_TEACHER_WITH_ONGOING_COURSES);
                    throw new ValidationException($error, $message);
                }
            }
        }
    }
}
