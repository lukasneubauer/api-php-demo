<?php

declare(strict_types=1);

namespace Tests;

use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordSettings;

final class PasswordSettingsWithPredefinedValues extends PasswordSettings
{
    public function __construct()
    {
        parent::__construct(
            PasswordAlgorithms::ARGON2I,
            13,
            4,
            65536
        );
    }
}
