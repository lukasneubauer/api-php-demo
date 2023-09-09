<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Password;
use App\Entities\User;
use App\Generators\UuidGenerator;
use DateTime;

class UserFactory
{
    private UuidGenerator $uuidGenerator;

    private TokenFactory $tokenFactory;

    public function __construct(
        UuidGenerator $uuidGenerator,
        TokenFactory $tokenFactory
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->tokenFactory = $tokenFactory;
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
            $this->uuidGenerator->generateUuid(),
            $firstName,
            $lastName,
            $avatar,
            $email,
            $password,
            $timezone,
            $this->tokenFactory->create($now),
            false,
            $now,
            $now
        );
    }
}
