<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MalformedJsonInRequestBody;
use PHPUnit\Framework\TestCase;

final class MalformedJsonInRequestBodyTest extends TestCase
{
    public function testCheckIfThereIsMalformedJsonInRequestBodyDoesNotThrowException(): void
    {
        try {
            $validator = new MalformedJsonInRequestBody();
            $validator->checkIfThereIsMalformedJsonInRequestBody([]);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfThereIsMalformedJsonInRequestBodyThrowsException(): void
    {
        try {
            $validator = new MalformedJsonInRequestBody();
            $validator->checkIfThereIsMalformedJsonInRequestBody(null);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(9, $data['error']['code']);
            $this->assertSame('Malformed JSON in request body.', $data['error']['message']);
            $this->assertSame('Malformed JSON in request body.', $e->getMessage());
        }
    }
}
