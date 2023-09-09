<?php

declare(strict_types=1);

use App\Generators\UuidGenerator;

function generate_uuid(): string
{
    return (new UuidGenerator())->generateUuid();
}
