<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\UsageOfIncorrectHttpMethod;
use PHPUnit\Framework\TestCase;

final class UsageOfIncorrectHttpMethodTest extends TestCase
{
    public function testCheckIfHttpMethodIsCorrectDoesNotThrowException(): void
    {
        try {
            $validator = new UsageOfIncorrectHttpMethod('EXPECTED_METHOD');
            $validator->checkIfHttpMethodIsCorrect('EXPECTED_METHOD');
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfHttpMethodIsCorrectThrowsException(): void
    {
        try {
            $validator = new UsageOfIncorrectHttpMethod('EXPECTED_METHOD');
            $validator->checkIfHttpMethodIsCorrect('NOT_EXPECTED_METHOD');
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(4, $data['error']['code']);
            $this->assertSame("Usage of incorrect http method 'NOT_EXPECTED_METHOD'. 'EXPECTED_METHOD' was expected.", $data['error']['message']);
            $this->assertSame("Usage of incorrect http method 'NOT_EXPECTED_METHOD'. 'EXPECTED_METHOD' was expected.", $e->getMessage());
        }
    }
}
