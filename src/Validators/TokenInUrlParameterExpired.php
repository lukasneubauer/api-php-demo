<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\TokenExpiredException;
use App\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class TokenInUrlParameterExpired
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws TokenExpiredException
     */
    public function checkIfTokenInUrlParameterHasExpired(ParameterBag $parameters): void
    {
        $token = $parameters->get('token');
        $user = $this->userRepository->getByToken($token);
        if ($user->getToken()->isExpired()) {
            $data = Error::tokenHasExpired();
            $message = Emsg::TOKEN_HAS_EXPIRED;
            throw new TokenExpiredException($data, $message);
        }
    }
}
