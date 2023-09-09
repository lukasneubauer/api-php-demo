<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\SecurityCode;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\UserDoesNotHaveAnySecurityCode;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class UserDoesNotHaveAnySecurityCodeTest extends TestCase
{
    public function testCheckIfUserDoesHaveAnySecurityCodeDoesNotThrowException(): void
    {
        try {
            $securityCode = m::mock(SecurityCode::class);
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
            $validator = new UserDoesNotHaveAnySecurityCode($userRepository);
            $validator->checkIfUserDoesHaveAnySecurityCode(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '123456789',
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserDoesHaveAnySecurityCodeThrowsException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('getSecurityCode')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new UserDoesNotHaveAnySecurityCode($userRepository);
            $validator->checkIfUserDoesHaveAnySecurityCode(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '123456789',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(48, $data['error']['code']);
            $this->assertSame('User does not have any security code.', $data['error']['message']);
            $this->assertSame('User does not have any security code.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
