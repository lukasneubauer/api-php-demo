<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\ExpectedDifferentDataTypeForPropertyInRequestBody;
use PHPUnit\Framework\TestCase;

final class ExpectedDifferentDataTypeForPropertyInRequestBodyTest extends TestCase
{
    public function testCheckIfPropertyIsOfCorrectDataTypeDoesNotThrowException(): void
    {
        try {
            $validator = new ExpectedDifferentDataTypeForPropertyInRequestBody('string', 'email');
            $validator->checkIfPropertyIsOfCorrectDataType(['email' => '']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfPropertyIsOfCorrectDataTypeThrowsException(): void
    {
        try {
            $validator = new ExpectedDifferentDataTypeForPropertyInRequestBody('string', 'email');
            $validator->checkIfPropertyIsOfCorrectDataType(['email' => 0]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(11, $data['error']['code']);
            $this->assertSame("Expected string in 'email', but got integer in request body.", $data['error']['message']);
            $this->assertSame("Expected string in 'email', but got integer in request body.", $e->getMessage());
        }
    }
}
