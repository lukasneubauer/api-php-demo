<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\SelectedTimezoneIsInvalid;
use PHPUnit\Framework\TestCase;

final class SelectedTimezoneIsInvalidTest extends TestCase
{
    public function testCheckIfSelectedTimezoneIsInvalidDoesNotThrowException(): void
    {
        try {
            $validator = new SelectedTimezoneIsInvalid();
            $validator->checkIfSelectedTimezoneIsInvalid(['timezone' => 'UTC']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfSelectedTimezoneIsInvalidThrowsException(): void
    {
        try {
            $validator = new SelectedTimezoneIsInvalid();
            $validator->checkIfSelectedTimezoneIsInvalid(['timezone' => 'XYZ']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(57, $data['error']['code']);
            $this->assertSame("Selected timezone 'XYZ' is invalid.", $data['error']['message']);
            $this->assertSame("Selected timezone 'XYZ' is invalid.", $e->getMessage());
        }
    }
}
