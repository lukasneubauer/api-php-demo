<?php

declare(strict_types=1);

namespace App\Checks;

class UuidCheck
{
    public function isUuidValid(string $uuid): bool
    {
        return \preg_match('#^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[ab89][0-9a-f]{3}-[0-9a-f]{12}$#', $uuid) === 1;
    }
}
