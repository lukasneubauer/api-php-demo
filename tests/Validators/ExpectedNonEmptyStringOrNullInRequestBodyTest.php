<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\ExpectedNonEmptyStringOrNullInRequestBody;
use PHPUnit\Framework\TestCase;

final class ExpectedNonEmptyStringOrNullInRequestBodyTest extends TestCase
{
    /**
     * @dataProvider getPasswords
     */
    public function testCheckIfPropertyIsNonEmptyStringOrNullDoesNotThrowException(?string $password): void
    {
        try {
            $validator = new ExpectedNonEmptyStringOrNullInRequestBody('password');
            $validator->checkIfPropertyIsNonEmptyStringOrNull(['password' => $password]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public static function getPasswords(): array
    {
        return [
            ['secret'],
            [null],
        ];
    }

    public function testCheckIfPropertyIsNonEmptyStringOrNullThrowsException(): void
    {
        try {
            $validator = new ExpectedNonEmptyStringOrNullInRequestBody('password');
            $validator->checkIfPropertyIsNonEmptyStringOrNull(['password' => '']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(12, $data['error']['code']);
            $this->assertSame("Expected value in 'password', but got \"\" (empty string) in request body.", $data['error']['message']);
            $this->assertSame("Expected value in 'password', but got \"\" (empty string) in request body.", $e->getMessage());
        }
    }
}
