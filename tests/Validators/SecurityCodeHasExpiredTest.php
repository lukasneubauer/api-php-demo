<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\SecurityCode;
use App\Entities\User;
use App\Exceptions\SecurityCodeExpiredException;
use App\Repositories\UserRepository;
use App\Validators\SecurityCodeHasExpired;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class SecurityCodeHasExpiredTest extends TestCase
{
    public function testCheckIfSecurityCodeHasExpiredDoesNotThrowException(): void
    {
        try {
            $securityCode = m::mock(SecurityCode::class)
                ->shouldReceive('isExpired')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getSecurityCode')
                ->times(1)
                ->andReturn($securityCode)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new SecurityCodeHasExpired($userRepository);
            $validator->checkIfSecurityCodeHasExpired(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '1234567890',
                ]
            );
            $this->assertTrue(true);
        } catch (SecurityCodeExpiredException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfSecurityCodeHasExpiredThrowsException(): void
    {
        try {
            $securityCode = m::mock(SecurityCode::class)
                ->shouldReceive('isExpired')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getSecurityCode')
                ->times(1)
                ->andReturn($securityCode)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new SecurityCodeHasExpired($userRepository);
            $validator->checkIfSecurityCodeHasExpired(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '1234567890',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (SecurityCodeExpiredException $e) {
            $data = $e->getData();
            $this->assertSame(45, $data['error']['code']);
            $this->assertSame("Security code has expired. New security code has been generated and sent on user's email address.", $data['error']['message']);
            $this->assertSame("Security code has expired. New security code has been generated and sent on user's email address.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
