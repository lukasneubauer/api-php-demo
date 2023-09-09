<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class ToAcceptThisRequestTheUserHasToBeTeacher
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfTheUserIsTeacherToAcceptThisRequest(HeaderBag $headers): void
    {
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));
        if ($session->getUser()->isTeacher() === false) {
            $data = Error::toAcceptThisRequestTheUserHasToBeTeacher();
            $message = \sprintf(Emsg::TO_ACCEPT_THIS_REQUEST_THE_USER_HAS_TO_BE_TEACHER);
            throw new ValidationException($data, $message);
        }
    }
}
