<?php

declare(strict_types=1);

namespace Tests\App\PersisterErrors;

use App\Exceptions\CouldNotPersistException;
use App\PersisterErrors\CouldNotGenerateUniqueValue;
use PHPUnit\Framework\TestCase;

final class CouldNotGenerateUniqueValueTest extends TestCase
{
    public function testThrowException(): void
    {
        try {
            $error = new CouldNotGenerateUniqueValue();
            $error->throwException('token', 5);
            $this->fail('Failed to throw exception.');
        } catch (CouldNotPersistException $e) {
            $data = $e->getData();
            $this->assertSame(25, $data['error']['code']);
            $this->assertSame("Could not generate unique value for 'token' in 5 tries.", $data['error']['message']);
            $this->assertSame("Could not generate unique value for 'token' in 5 tries.", $e->getMessage());
        }
    }
}
