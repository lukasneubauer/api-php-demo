<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\SecurityCode;
use App\Entities\User;
use App\Exceptions\SecurityCodeHasToBeGeneratedAgainException;
use App\Repositories\UserRepository;
use App\Validators\SecurityCodeHasBeenGeneratedAgain;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class SecurityCodeHasBeenGeneratedAgainTest extends TestCase
{
    public function testCheckIfSecurityCodeHasToBeGeneratedAgainDoesNotThrowException(): void
    {
        try {
            $securityCode = m::mock(SecurityCode::class);
            $securityCode->shouldReceive('getCode')
                ->times(1)
                ->andReturn('123456789');
            $securityCode->shouldReceive('getInputFailures')
                ->times(1)
                ->andReturn(0);
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
            $validator = new SecurityCodeHasBeenGeneratedAgain($userRepository);
            $validator->checkIfSecurityCodeHasToBeGeneratedAgain(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '123456789',
                ]
            );
            $this->assertTrue(true);
        } catch (SecurityCodeHasToBeGeneratedAgainException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfSecurityCodeHasToBeGeneratedAgainThrowsException(): void
    {
        try {
            $securityCode = m::mock(SecurityCode::class);
            $securityCode->shouldReceive('getCode')
                ->times(1)
                ->andReturn('123456789');
            $securityCode->shouldReceive('getInputFailures')
                ->times(1)
                ->andReturn(2);
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
            $validator = new SecurityCodeHasBeenGeneratedAgain($userRepository);
            $validator->checkIfSecurityCodeHasToBeGeneratedAgain(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '000000000',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (SecurityCodeHasToBeGeneratedAgainException $e) {
            $data = $e->getData();
            $this->assertSame(44, $data['error']['code']);
            $this->assertSame("Incorrect security code has been entered 3 or more times in a row. New security code has been generated and sent on user's email address.", $data['error']['message']);
            $this->assertSame("Incorrect security code has been entered 3 or more times in a row. New security code has been generated and sent on user's email address.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
