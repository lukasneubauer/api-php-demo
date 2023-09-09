<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Course;
use App\Entities\Subject;
use App\Entities\User;
use App\Generators\UuidGenerator;
use DateTime;

class CourseFactory
{
    private UuidGenerator $uuidGenerator;

    public function __construct(UuidGenerator $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(
        Subject $subject,
        User $teacher,
        ?string $name,
        int $price,
        DateTime $now
    ): Course {
        return new Course(
            $this->uuidGenerator->generateUuid(),
            $subject,
            $teacher,
            $name,
            $price,
            false,
            $now,
            $now
        );
    }
}
