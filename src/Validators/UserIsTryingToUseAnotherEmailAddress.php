<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class UserIsTryingToUseAnotherEmailAddress
{
    private SessionRepository $sessionRepository;

    public function __construct(SessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfUserIsTryingToUseAnotherEmailAddress(HeaderBag $headers, array $data): void
    {
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));
        if ($session->getUser()->getEmail() !== $data['email']) {
            $error = Error::userIsTryingToUseAnotherEmailAddress();
            $message = \sprintf(Emsg::USER_IS_TRYING_TO_USE_ANOTHER_EMAIL_ADDRESS);
            throw new ValidationException($error, $message);
        }
    }
}
