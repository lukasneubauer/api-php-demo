<?php

declare(strict_types=1);

namespace Tests\App\Database;

use App\Database\UniqueKey;
use PHPUnit\Framework\TestCase;

final class UniqueKeyTest extends TestCase
{
    public function testExtractUniqueKeyFromExceptionMessage(): void
    {
        $message = "An exception occurred while executing...\n"
            . "... random line 1...\n"
            . "... random line 2...\n"
            . "... random line 3...\n"
            . "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'foo@example.com' for key 'UNIQ_0123456789'";
        $keyName = (new UniqueKey())->extractUniqueKeyFromExceptionMessage($message);
        $this->assertSame('UNIQ_0123456789', $keyName);
    }
}
