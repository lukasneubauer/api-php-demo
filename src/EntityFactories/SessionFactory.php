<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Session;
use App\Entities\User;
use App\Generators\ApiTokenGenerator;
use App\Generators\UuidGenerator;
use DateTime;

class SessionFactory
{
    private UuidGenerator $uuidGenerator;

    private ApiTokenGenerator $apiTokenGenerator;

    public function __construct(
        UuidGenerator $uuidGenerator,
        ApiTokenGenerator $apiTokenGenerator
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->apiTokenGenerator = $apiTokenGenerator;
    }

    public function create(
        User $user,
        string $apiClientId,
        DateTime $now
    ): Session {
        return new Session(
            $this->uuidGenerator->generateUuid(),
            $user,
            $apiClientId,
            $this->apiTokenGenerator->generateApiToken(),
            $now,
            $now,
            $now
        );
    }
}
