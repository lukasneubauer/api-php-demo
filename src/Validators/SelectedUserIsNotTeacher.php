<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

class SelectedUserIsNotTeacher
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfSelectedUserIsTeacher(array $data): void
    {
        $user = $this->userRepository->getById($data['teacherId']);
        if ($user->isTeacher() === false) {
            $error = Error::selectedUserIsNotTeacher();
            $message = \sprintf(Emsg::SELECTED_USER_IS_NOT_TEACHER);
            throw new ValidationException($error, $message);
        }
    }
}
