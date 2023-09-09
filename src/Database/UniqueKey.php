<?php

declare(strict_types=1);

namespace App\Database;

class UniqueKey
{
    public function extractUniqueKeyFromExceptionMessage(string $message): string
    {
        $lines = \explode("\n", $message);
        $sqlState = \array_pop($lines);

        return \substr($sqlState, \strpos($sqlState, 'UNIQ_'), -1);
    }
}
