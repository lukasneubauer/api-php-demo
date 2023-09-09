<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Token;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\UserNotFoundByEmailCredentialsInUrlParameters;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class UserNotFoundByEmailCredentialsInUrlParametersTest extends TestCase
{
    public function testCheckIfUserEmailCredentialsInUrlParametersAreCorrectDoesNotThrowException(): void
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
            $validator = new UserNotFoundByEmailCredentialsInUrlParameters($userRepository);
            $validator->checkIfUserEmailCredentialsInUrlParametersAreCorrect(new ParameterBag(['email' => 'john.doe@example.com', 'token' => '1234567890']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserEmailCredentialsInUrlParametersAreCorrectThrowsExceptionBecauseOfInvalidEmail(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new UserNotFoundByEmailCredentialsInUrlParameters($userRepository);
            $validator->checkIfUserEmailCredentialsInUrlParametersAreCorrect(new ParameterBag(['email' => 'john.doe@example.com', 'token' => '1234567890']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(7, $data['error']['code']);
            $this->assertSame("No data found for 'email' url parameter.", $data['error']['message']);
            $this->assertSame("No data found for 'email' url parameter.", $e->getMessage());
        }
    }

    public function testCheckIfUserEmailCredentialsInUrlParametersAreCorrectThrowsExceptionBecauseOfNotExistingToken(): void
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
            $validator = new UserNotFoundByEmailCredentialsInUrlParameters($userRepository);
            $validator->checkIfUserEmailCredentialsInUrlParametersAreCorrect(new ParameterBag(['email' => 'john.doe@example.com', 'token' => '1234567890']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(7, $data['error']['code']);
            $this->assertSame("No data found for 'token' url parameter.", $data['error']['message']);
            $this->assertSame("No data found for 'token' url parameter.", $e->getMessage());
        }
    }

    public function testCheckIfUserEmailCredentialsInUrlParametersAreCorrectThrowsExceptionBecauseOfInvalidToken(): void
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
            $validator = new UserNotFoundByEmailCredentialsInUrlParameters($userRepository);
            $validator->checkIfUserEmailCredentialsInUrlParametersAreCorrect(new ParameterBag(['email' => 'john.doe@example.com', 'token' => '1234567890']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(7, $data['error']['code']);
            $this->assertSame("No data found for 'token' url parameter.", $data['error']['message']);
            $this->assertSame("No data found for 'token' url parameter.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
