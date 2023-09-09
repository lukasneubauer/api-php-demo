<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\LessonRepository;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class ToDeleteTheLessonTheUserHasToBeTeacher
{
    private SessionRepository $sessionRepository;

    private LessonRepository $lessonRepository;

    public function __construct(
        SessionRepository $sessionRepository,
        LessonRepository $lessonRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfTheUserIsTeacherToDeleteTheLesson(HeaderBag $headers, array $data): void
    {
        $lesson = $this->lessonRepository->getById($data['id']);
        $course = $lesson->getCourse();
        $teacher = $course->getTeacher();
        $apiToken = (string) $headers->get(ApiHeaders::API_TOKEN);
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        if ($teacher->getId() !== $user->getId()) {
            $error = Error::toDeleteTheLessonTheUserHasToBeTeacher();
            $message = \sprintf(Emsg::TO_DELETE_THE_LESSON_THE_USER_HAS_TO_BE_TEACHER);
            throw new ValidationException($error, $message);
        }
    }
}
