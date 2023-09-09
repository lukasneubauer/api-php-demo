<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\MissingMandatoryPropertyInRequestBody;
use PHPUnit\Framework\TestCase;

final class MissingMandatoryPropertyInRequestBodyTest extends TestCase
{
    public function testCheckIfPropertyIsMissingDoesNotThrowException(): void
    {
        try {
            $validator = new MissingMandatoryPropertyInRequestBody('email');
            $validator->checkIfPropertyIsMissing(['email' => '']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfPropertyIsMissingThrowsException(): void
    {
        try {
            $validator = new MissingMandatoryPropertyInRequestBody('email');
            $validator->checkIfPropertyIsMissing([]);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(10, $data['error']['code']);
            $this->assertSame("Missing mandatory property 'email' in request body.", $data['error']['message']);
            $this->assertSame("Missing mandatory property 'email' in request body.", $e->getMessage());
        }
    }
}
