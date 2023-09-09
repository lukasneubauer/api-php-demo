<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\SecurityCodeExpiredException;
use App\Repositories\UserRepository;

class SecurityCodeHasExpired
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws SecurityCodeExpiredException
     */
    public function checkIfSecurityCodeHasExpired(array $data): void
    {
        $user = $this->userRepository->getByEmail($data['email']);
        if ($user->getSecurityCode()->isExpired()) {
            $error = Error::securityCodeHasExpired();
            $message = Emsg::SECURITY_CODE_HAS_EXPIRED;
            throw new SecurityCodeExpiredException($error, $message);
        }
    }
}
