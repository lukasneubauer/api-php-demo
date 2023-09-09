<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\ExpectedDifferentDataTypeForNullablePropertyInRequestBody;
use PHPUnit\Framework\TestCase;

final class ExpectedDifferentDataTypeForNullablePropertyInRequestBodyTest extends TestCase
{
    /**
     * @dataProvider getValidPasswordValues
     */
    public function testCheckIfNullablePropertyIsOfCorrectDataTypeDoesNotThrowException(?string $password): void
    {
        try {
            $validator = new ExpectedDifferentDataTypeForNullablePropertyInRequestBody('string', 'password');
            $validator->checkIfNullablePropertyIsOfCorrectDataType(['password' => $password]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public static function getValidPasswordValues(): array
    {
        return [
            [''],
            [null],
        ];
    }

    public function testCheckIfNullablePropertyIsOfCorrectDataTypeThrowsException(): void
    {
        try {
            $validator = new ExpectedDifferentDataTypeForNullablePropertyInRequestBody('string', 'password');
            $validator->checkIfNullablePropertyIsOfCorrectDataType(['password' => 0]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(11, $data['error']['code']);
            $this->assertSame("Expected string or null in 'password', but got integer in request body.", $data['error']['message']);
            $this->assertSame("Expected string or null in 'password', but got integer in request body.", $e->getMessage());
        }
    }
}
