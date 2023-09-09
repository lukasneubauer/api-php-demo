<?php

declare(strict_types=1);

namespace Tests;

use App\Entities\Password;
use App\Entities\Token;
use App\Entities\User;
use App\EntityFactories\UserFactory;
use DateTime;

final class UserFactoryWithPredefinedToken extends UserFactory
{
    public function __construct()
    {
        // mute parent constructor
    }

    public function create(
        string $firstName,
        string $lastName,
        ?string $avatar,
        string $email,
        Password $password,
        string $timezone,
        DateTime $now
    ): User {
        return new User(
            \generate_uuid(),
            $firstName,
            $lastName,
            $avatar,
            $email,
            $password,
            $timezone,
            new Token((new TokenGeneratorWithPredefinedToken())->generateToken(), $now),
            false,
            $now,
            $now
        );
    }
}
