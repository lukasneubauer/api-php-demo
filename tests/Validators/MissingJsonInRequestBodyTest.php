<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MissingJsonInRequestBody;
use PHPUnit\Framework\TestCase;

final class MissingJsonInRequestBodyTest extends TestCase
{
    public function testCheckIfThereIsMissingJsonInRequestBodyDoesNotThrowException(): void
    {
        try {
            $validator = new MissingJsonInRequestBody();
            $validator->checkIfThereIsMissingJsonInRequestBody('{}');
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfThereIsMissingJsonInRequestBodyThrowsException(): void
    {
        try {
            $validator = new MissingJsonInRequestBody();
            $validator->checkIfThereIsMissingJsonInRequestBody('');
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(8, $data['error']['code']);
            $this->assertSame('Missing JSON in request body.', $data['error']['message']);
            $this->assertSame('Missing JSON in request body.', $e->getMessage());
        }
    }
}
