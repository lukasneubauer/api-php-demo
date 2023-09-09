<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Checks\EmailCheck;
use App\Exceptions\ValidationException;
use App\Validators\MalformedEmailInUrlParameter;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class MalformedEmailInUrlParameterTest extends TestCase
{
    public function testCheckIfEmailUrlParameterIsMalformedDoesNotThrowException(): void
    {
        try {
            $emailCheck = m::mock(EmailCheck::class)
                ->shouldReceive('isEmailInValidFormat')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $validator = new MalformedEmailInUrlParameter($emailCheck);
            $validator->checkIfEmailUrlParameterIsMalformed(new ParameterBag(['email' => 'john.doe@example.com']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfEmailUrlParameterIsMalformedThrowsException(): void
    {
        try {
            $emailCheck = m::mock(EmailCheck::class)
                ->shouldReceive('isEmailInValidFormat')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $validator = new MalformedEmailInUrlParameter($emailCheck);
            $validator->checkIfEmailUrlParameterIsMalformed(new ParameterBag(['email' => 'john.doe.example.com']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(16, $data['error']['code']);
            $this->assertSame('Malformed email.', $data['error']['message']);
            $this->assertSame('Malformed email.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
