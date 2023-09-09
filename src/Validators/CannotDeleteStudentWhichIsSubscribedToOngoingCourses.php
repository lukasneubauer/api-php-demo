<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class CannotDeleteStudentWhichIsSubscribedToOngoingCourses
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses(array $data): void
    {
        $user = $this->userRepository->getById($data['id']);
        $courses = $user->getStudentCourses();

        if ($user->isStudent() && \count($courses) > 0) {
            foreach ($courses as $course) {
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

                if ($courseIsOngoing) {
                    $error = Error::cannotDeleteStudentWhichIsSubscribedToOngoingCourses();
                    $message = \sprintf(Emsg::CANNOT_DELETE_STUDENT_WHICH_IS_SUBSCRIBED_TO_ONGOING_COURSES);
                    throw new ValidationException($error, $message);
                }
            }
        }
    }
}
