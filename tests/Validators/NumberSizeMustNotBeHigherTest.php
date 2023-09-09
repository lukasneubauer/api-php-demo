<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\NumberSizeMustNotBeHigher;
use PHPUnit\Framework\TestCase;

final class NumberSizeMustNotBeHigherTest extends TestCase
{
    public function testCheckIfNumberSizeIsHigherDoesNotThrowException(): void
    {
        try {
            $validator = new NumberSizeMustNotBeHigher('price', 4294967295);
            $validator->checkIfNumberSizeIsHigher(['price' => 4294967295]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfNumberSizeIsHigherThrowsException(): void
    {
        try {
            $validator = new NumberSizeMustNotBeHigher('price', 4294967295);
            $validator->checkIfNumberSizeIsHigher(['price' => 4294967296]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(56, $data['error']['code']);
            $this->assertSame("Number size of property 'price' must not be higher than 4294967295.", $data['error']['message']);
            $this->assertSame("Number size of property 'price' must not be higher than 4294967295.", $e->getMessage());
        }
    }
}
