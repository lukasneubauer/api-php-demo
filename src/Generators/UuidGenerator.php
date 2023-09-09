<?php

declare(strict_types=1);

namespace App\Generators;

use Ramsey\Uuid\Uuid;

class UuidGenerator
{
    public function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }
}
