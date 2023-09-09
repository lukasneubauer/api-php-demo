<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\ExpectedNonEmptyStringInRequestBody;
use PHPUnit\Framework\TestCase;

final class ExpectedNonEmptyStringInRequestBodyTest extends TestCase
{
    public function testCheckIfPropertyIsNonEmptyStringDoesNotThrowException(): void
    {
        try {
            $validator = new ExpectedNonEmptyStringInRequestBody('email');
            $validator->checkIfPropertyIsNonEmptyString(['email' => 'john.doe@example.com']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfPropertyIsNonEmptyStringThrowsException(): void
    {
        try {
            $validator = new ExpectedNonEmptyStringInRequestBody('email');
            $validator->checkIfPropertyIsNonEmptyString(['email' => '']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(12, $data['error']['code']);
            $this->assertSame("Expected value in 'email', but got \"\" (empty string) in request body.", $data['error']['message']);
            $this->assertSame("Expected value in 'email', but got \"\" (empty string) in request body.", $e->getMessage());
        }
    }
}
