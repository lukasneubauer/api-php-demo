<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\TokenExpiredException;
use App\Repositories\UserRepository;

class TokenExpired
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws TokenExpiredException
     */
    public function checkIfTokenHasExpired(array $data): void
    {
        $user = $this->userRepository->getByEmail($data['email']);
        if ($user->getToken()->isExpired()) {
            $error = Error::tokenHasExpired();
            $message = Emsg::TOKEN_HAS_EXPIRED;
            throw new TokenExpiredException($error, $message);
        }
    }
}
