<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Generators\UuidGenerator;
use DateTime;

class LessonFactory
{
    private UuidGenerator $uuidGenerator;

    public function __construct(UuidGenerator $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(
        Course $course,
        DateTime $from,
        DateTime $to,
        string $name,
        DateTime $now
    ): Lesson {
        return new Lesson(
            $this->uuidGenerator->generateUuid(),
            $course,
            $from,
            $to,
            $name,
            $now,
            $now
        );
    }
}
