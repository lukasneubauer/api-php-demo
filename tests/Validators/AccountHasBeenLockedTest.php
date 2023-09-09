<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Password;
use App\Entities\User;
use App\Exceptions\LockAccountException;
use App\Exceptions\ValidationException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;
use App\Validators\AccountHasBeenLocked;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class AccountHasBeenLockedTest extends TestCase
{
    public function testCheckIfAccountHasBeenLockedDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class);
            $user->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi', PasswordAlgorithms::BCRYPT));
            $user->shouldReceive('getAuthenticationFailures')
                ->times(1)
                ->andReturn(0);
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
            $validator = new AccountHasBeenLocked($userRepository, $passwordCheck);
            $validator->checkIfAccountHasBeenLocked(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->assertTrue(true);
        } catch (LockAccountException $e) {
            $this->fail($e->getMessage());
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfAccountHasBeenLockedThrowsLockAccountException(): void
    {
        try {
            $user = m::mock(User::class);
            $user->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi', PasswordAlgorithms::BCRYPT));
            $user->shouldReceive('getAuthenticationFailures')
                ->times(1)
                ->andReturn(2);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $passwordCheck = m::mock(PasswordCheck::class)
                ->shouldReceive('isPasswordCorrect')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $validator = new AccountHasBeenLocked($userRepository, $passwordCheck);
            $validator->checkIfAccountHasBeenLocked(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (LockAccountException $e) {
            $data = $e->getData();
            $this->assertSame(41, $data['error']['code']);
            $this->assertSame('Incorrect password has been entered 3 or more times in a row. Account has been locked for security reasons.', $data['error']['message']);
            $this->assertSame('Incorrect password has been entered 3 or more times in a row. Account has been locked for security reasons.', $e->getMessage());
        }
    }

    public function testCheckIfAccountHasBeenLockedThrowsValidationException(): void
    {
        try {
            $user = m::mock(User::class);
            $user->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi', PasswordAlgorithms::BCRYPT));
            $user->shouldReceive('getAuthenticationFailures')
                ->times(1)
                ->andReturn(3);
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
                ->andReturn(false)
                ->getMock();
            $validator = new AccountHasBeenLocked($userRepository, $passwordCheck);
            $validator->checkIfAccountHasBeenLocked(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(41, $data['error']['code']);
            $this->assertSame('Incorrect password has been entered 3 or more times in a row. Account has been locked for security reasons.', $data['error']['message']);
            $this->assertSame('Incorrect password has been entered 3 or more times in a row. Account has been locked for security reasons.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
