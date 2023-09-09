<?php

declare(strict_types=1);

namespace App\Passwords;

use App\Exceptions\PasswordHashingFailedException;
use ValueError;

class PasswordEncoder
{
    private PasswordSettings $passwordSettings;

    public function __construct(PasswordSettings $passwordSettings)
    {
        $this->passwordSettings = $passwordSettings;
    }

    /**
     * @throws PasswordHashingFailedException
     */
    public function hashPassword(string $plainPassword): string
    {
        try {
            $hash = \password_hash($plainPassword, $this->passwordSettings->getAlgorithmPhpInternalConstantValue(), $this->passwordSettings->getOptions());
        } catch (ValueError $e) {
            throw new PasswordHashingFailedException();
        }

        return $hash;
    }
}
