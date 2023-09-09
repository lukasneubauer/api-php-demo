<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Token;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\UserNotFoundByEmailCredentials;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class UserNotFoundByEmailCredentialsTest extends TestCase
{
    public function testCheckIfUserEmailCredentialsAreCorrectDoesNotThrowException(): void
    {
        try {
            $token = m::mock(Token::class)
                ->shouldReceive('getCode')
                ->times(1)
                ->andReturn('1234567890')
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getToken')
                ->times(2)
                ->andReturn($token)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new UserNotFoundByEmailCredentials($userRepository);
            $validator->checkIfUserEmailCredentialsAreCorrect(
                [
                    'email' => 'john.doe@example.com',
                    'token' => '1234567890',
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserEmailCredentialsAreCorrectThrowsExceptionBecauseOfInvalidEmail(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new UserNotFoundByEmailCredentials($userRepository);
            $validator->checkIfUserEmailCredentialsAreCorrect(
                [
                    'email' => 'john.doe@example.com',
                    'token' => '1234567890',
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

    public function testCheckIfUserEmailCredentialsAreCorrectThrowsExceptionBecauseOfNotExistingToken(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('getToken')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new UserNotFoundByEmailCredentials($userRepository);
            $validator->checkIfUserEmailCredentialsAreCorrect(
                [
                    'email' => 'john.doe@example.com',
                    'token' => '1234567890',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(13, $data['error']['code']);
            $this->assertSame("No data found for 'token' in request body.", $data['error']['message']);
            $this->assertSame("No data found for 'token' in request body.", $e->getMessage());
        }
    }

    public function testCheckIfUserEmailCredentialsAreCorrectThrowsExceptionBecauseOfInvalidToken(): void
    {
        try {
            $token = m::mock(Token::class)
                ->shouldReceive('getCode')
                ->times(1)
                ->andReturn('incorrect')
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getToken')
                ->times(2)
                ->andReturn($token)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new UserNotFoundByEmailCredentials($userRepository);
            $validator->checkIfUserEmailCredentialsAreCorrect(
                [
                    'email' => 'john.doe@example.com',
                    'token' => '1234567890',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(13, $data['error']['code']);
            $this->assertSame("No data found for 'token' in request body.", $data['error']['message']);
            $this->assertSame("No data found for 'token' in request body.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
