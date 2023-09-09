<?php

declare(strict_types=1);

namespace App\Passwords;

class PasswordNeedsRehashCheck
{
    private PasswordSettings $passwordSettings;

    public function __construct(PasswordSettings $passwordSettings)
    {
        $this->passwordSettings = $passwordSettings;
    }

    public function doesPasswordNeedRehash(string $hash): bool
    {
        return \password_needs_rehash($hash, $this->passwordSettings->getAlgorithmPhpInternalConstantValue(), $this->passwordSettings->getOptions());
    }
}
