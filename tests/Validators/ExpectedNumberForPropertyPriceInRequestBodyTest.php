<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\ExpectedNumberForPropertyPriceInRequestBody;
use PHPUnit\Framework\TestCase;

final class ExpectedNumberForPropertyPriceInRequestBodyTest extends TestCase
{
    public function testCheckIfPropertyPriceIsNumberDoesNotThrowException(): void
    {
        try {
            $validator = new ExpectedNumberForPropertyPriceInRequestBody();
            $validator->checkIfPropertyPriceIsNumber(['price' => 25000]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfPropertyPriceIsNumberThrowsException(): void
    {
        try {
            $validator = new ExpectedNumberForPropertyPriceInRequestBody();
            $validator->checkIfPropertyPriceIsNumber(['price' => '']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(11, $data['error']['code']);
            $this->assertSame("Expected integer in 'price', but got string in request body.", $data['error']['message']);
            $this->assertSame("Expected integer in 'price', but got string in request body.", $e->getMessage());
        }
    }
}
