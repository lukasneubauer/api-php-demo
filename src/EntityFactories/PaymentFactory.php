<?php

declare(strict_types=1);

namespace App\EntityFactories;

use App\Entities\Course;
use App\Entities\Payment;
use App\Entities\User;
use App\Generators\UuidGenerator;
use DateTime;

class PaymentFactory
{
    private UuidGenerator $uuidGenerator;

    public function __construct(UuidGenerator $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function create(
        Course $course,
        User $student,
        int $price,
        DateTime $now
    ): Payment {
        return new Payment(
            $this->uuidGenerator->generateUuid(),
            $course,
            $student,
            $price,
            $now,
            $now
        );
    }
}
