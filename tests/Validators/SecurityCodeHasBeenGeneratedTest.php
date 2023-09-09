<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Password;
use App\Entities\User;
use App\Exceptions\SecurityCodeHasToBeGeneratedException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;
use App\Validators\SecurityCodeHasBeenGenerated;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class SecurityCodeHasBeenGeneratedTest extends TestCase
{
    public function testCheckIfSecurityCodeHasToBeGeneratedDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class);
            $user->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi', PasswordAlgorithms::BCRYPT));
            $user->shouldReceive('isLocked')
                ->times(1)
                ->andReturn(false);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $passwordCheck = m::mock(PasswordCheck::class)
                ->shouldReceive('isPasswordCorrect')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $validator = new SecurityCodeHasBeenGenerated($userRepository, $passwordCheck);
            $validator->checkIfSecurityCodeHasToBeGenerated(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->assertTrue(true);
        } catch (SecurityCodeHasToBeGeneratedException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfSecurityCodeHasToBeGeneratedThrowsException(): void
    {
        try {
            $user = m::mock(User::class);
            $user->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi', PasswordAlgorithms::BCRYPT));
            $user->shouldReceive('isLocked')
                ->times(1)
                ->andReturn(true);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $passwordCheck = m::mock(PasswordCheck::class)
                ->shouldReceive('isPasswordCorrect')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $validator = new SecurityCodeHasBeenGenerated($userRepository, $passwordCheck);
            $validator->checkIfSecurityCodeHasToBeGenerated(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (SecurityCodeHasToBeGeneratedException $e) {
            $data = $e->getData();
            $this->assertSame(42, $data['error']['code']);
            $this->assertSame("User's authentication was successful, but since there were 3 or more failed login attempts in a row in the past, a security code has been generated and sent on user's email address.", $data['error']['message']);
            $this->assertSame("User's authentication was successful, but since there were 3 or more failed login attempts in a row in the past, a security code has been generated and sent on user's email address.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
