<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Subject;
use App\Entities\User;
use App\Generators\UuidGenerator;
use DateTime;

class SubjectFactory
{
    private UuidGenerator $uuidGenerator;

    public function __construct(UuidGenerator $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(
        User $createdBy,
        string $name,
        DateTime $now
    ): Subject {
        return new Subject(
            $this->uuidGenerator->generateUuid(),
            $createdBy,
            $name,
            $now,
            $now
        );
    }
}
