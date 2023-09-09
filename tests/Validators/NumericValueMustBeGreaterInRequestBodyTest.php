<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\NumericValueMustBeGreaterInRequestBody;
use PHPUnit\Framework\TestCase;

final class NumericValueMustBeGreaterInRequestBodyTest extends TestCase
{
    public function testCheckIfPropertyNumericValueIsGreaterDoesNotThrowException(): void
    {
        try {
            $validator = new NumericValueMustBeGreaterInRequestBody('price', 0);
            $validator->checkIfPropertyNumericValueIsGreater(['price' => 25000]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfPropertyNumericValueIsGreaterThrowsException(): void
    {
        try {
            $validator = new NumericValueMustBeGreaterInRequestBody('price', 0);
            $validator->checkIfPropertyNumericValueIsGreater(['price' => 0]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(15, $data['error']['code']);
            $this->assertSame("Numeric value for 'price' must be greater than 0, but got 0.", $data['error']['message']);
            $this->assertSame("Numeric value for 'price' must be greater than 0, but got 0.", $e->getMessage());
        }
    }
}
