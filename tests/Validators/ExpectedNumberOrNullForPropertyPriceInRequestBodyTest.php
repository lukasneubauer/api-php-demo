<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\ExpectedNumberOrNullForPropertyPriceInRequestBody;
use PHPUnit\Framework\TestCase;

final class ExpectedNumberOrNullForPropertyPriceInRequestBodyTest extends TestCase
{
    /**
     * @dataProvider getPrices
     */
    public function testCheckIfPropertyPriceIsNumberOrNullDoesNotThrowException(?int $price): void
    {
        try {
            $validator = new ExpectedNumberOrNullForPropertyPriceInRequestBody();
            $validator->checkIfPropertyPriceIsNumberOrNull(['price' => $price]);
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

    public function testCheckIfPropertyPriceIsNumberOrNullThrowsException(): void
    {
        try {
            $validator = new ExpectedNumberOrNullForPropertyPriceInRequestBody();
            $validator->checkIfPropertyPriceIsNumberOrNull(['price' => '']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(11, $data['error']['code']);
            $this->assertSame("Expected integer or null in 'price', but got string in request body.", $data['error']['message']);
            $this->assertSame("Expected integer or null in 'price', but got string in request body.", $e->getMessage());
        }
    }
}
