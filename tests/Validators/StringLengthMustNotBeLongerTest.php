<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\StringLengthMustNotBeLonger;
use PHPUnit\Framework\TestCase;

final class StringLengthMustNotBeLongerTest extends TestCase
{
    public function testCheckIfStringLengthIsLongerDoesNotThrowException(): void
    {
        try {
            $validator = new StringLengthMustNotBeLonger('firstName', 255);
            $validator->checkIfStringLengthIsLonger(['firstName' => \str_repeat('a', 255)]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfStringLengthIsLongerThrowsException(): void
    {
        try {
            $validator = new StringLengthMustNotBeLonger('firstName', 255);
            $validator->checkIfStringLengthIsLonger(['firstName' => \str_repeat('a', 256)]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(55, $data['error']['code']);
            $this->assertSame("String length of property 'firstName' must not be longer than 255 characters.", $data['error']['message']);
            $this->assertSame("String length of property 'firstName' must not be longer than 255 characters.", $e->getMessage());
        }
    }
}
