<?php

declare(strict_types=1);

namespace App\Passwords;

use App\Entities\Password;
use App\Exceptions\PasswordHashingFailedException;

class PasswordRehasher
{
    private PasswordNeedsRehashCheck $passwordNeedsRehashCheck;

    private PasswordEncoderEntityFactory $passwordEncoderEntityFactory;

    public function __construct(
        PasswordNeedsRehashCheck $passwordNeedsRehashCheck,
        PasswordEncoderEntityFactory $passwordEncoderEntityFactory
    ) {
        $this->passwordNeedsRehashCheck = $passwordNeedsRehashCheck;
        $this->passwordEncoderEntityFactory = $passwordEncoderEntityFactory;
    }

    /**
     * @throws PasswordHashingFailedException
     */
    public function rehashPassword(string $plainPassword, Password $password): Password
    {
        if ($this->passwordNeedsRehashCheck->doesPasswordNeedRehash($password->getHash())) {
            return $this->passwordEncoderEntityFactory->createPassword($plainPassword);
        }

        return $password;
    }
}
