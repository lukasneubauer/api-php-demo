<?php

declare(strict_types=1);

namespace Tests\App\PersisterErrors;

use App\Exceptions\CouldNotPersistException;
use App\PersisterErrors\ValueIsAlreadyTaken;
use PHPUnit\Framework\TestCase;

final class ValueIsAlreadyTakenTest extends TestCase
{
    public function testThrowException(): void
    {
        try {
            $error = new ValueIsAlreadyTaken();
            $error->throwException('email');
            $this->fail('Failed to throw exception.');
        } catch (CouldNotPersistException $e) {
            $data = $e->getData();
            $this->assertSame(14, $data['error']['code']);
            $this->assertSame("Value for 'email' in request body is already taken.", $data['error']['message']);
            $this->assertSame("Value for 'email' in request body is already taken.", $e->getMessage());
        }
    }
}
