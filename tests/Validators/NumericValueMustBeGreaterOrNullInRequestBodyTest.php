<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\NumericValueMustBeGreaterOrNullInRequestBody;
use PHPUnit\Framework\TestCase;

final class NumericValueMustBeGreaterOrNullInRequestBodyTest extends TestCase
{
    /**
     * @dataProvider getPrices
     */
    public function testCheckIfPropertyNumericValueIsGreaterOrNullDoesNotThrowException(?int $price): void
    {
        try {
            $validator = new NumericValueMustBeGreaterOrNullInRequestBody('price', 0);
            $validator->checkIfPropertyNumericValueIsGreaterOrNull(['price' => $price]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public static function getPrices(): array
    {
        return [
            [25000],
            [null],
        ];
    }

    public function testCheckIfPropertyNumericValueIsGreaterOrNullThrowsException(): void
    {
        try {
            $validator = new NumericValueMustBeGreaterOrNullInRequestBody('price', 0);
            $validator->checkIfPropertyNumericValueIsGreaterOrNull(['price' => 0]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(15, $data['error']['code']);
            $this->assertSame("Numeric value for 'price' must be greater than 0, but got 0.", $data['error']['message']);
            $this->assertSame("Numeric value for 'price' must be greater than 0, but got 0.", $e->getMessage());
        }
    }
}
