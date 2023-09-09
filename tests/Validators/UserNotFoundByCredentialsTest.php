<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Password;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;
use App\Validators\UserNotFoundByCredentials;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class UserNotFoundByCredentialsTest extends TestCase
{
    public function testCheckIfUserCredentialsAreCorrectDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi', PasswordAlgorithms::BCRYPT))
                ->getMock();
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
            $validator = new UserNotFoundByCredentials($userRepository, $passwordCheck);
            $validator->checkIfUserCredentialsAreCorrect(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserCredentialsAreCorrectThrowsExceptionBecauseOfInvalidEmail(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $passwordCheck = m::mock(PasswordCheck::class);
            $validator = new UserNotFoundByCredentials($userRepository, $passwordCheck);
            $validator->checkIfUserCredentialsAreCorrect(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(13, $data['error']['code']);
            $this->assertSame("No data found for 'email' in request body.", $data['error']['message']);
            $this->assertSame("No data found for 'email' in request body.", $e->getMessage());
        }
    }

    public function testCheckIfUserCredentialsAreCorrectThrowsExceptionBecauseOfInvalidPassword(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('incorrect', PasswordAlgorithms::BCRYPT))
                ->getMock();
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
            $validator = new UserNotFoundByCredentials($userRepository, $passwordCheck);
            $validator->checkIfUserCredentialsAreCorrect(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(13, $data['error']['code']);
            $this->assertSame("No data found for 'password' in request body.", $data['error']['message']);
            $this->assertSame("No data found for 'password' in request body.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
