<?php

declare(strict_types=1);

use App\Checks\UuidCheck;

function is_uuid_valid(string $uuid): bool
{
    return (new UuidCheck())->isUuidValid($uuid);
}
