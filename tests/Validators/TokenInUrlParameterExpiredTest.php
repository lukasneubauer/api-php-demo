<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Token;
use App\Entities\User;
use App\Exceptions\TokenExpiredException;
use App\Repositories\UserRepository;
use App\Validators\TokenInUrlParameterExpired;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class TokenInUrlParameterExpiredTest extends TestCase
{
    public function testCheckIfTokenInUrlParameterHasExpiredDoesNotThrowException(): void
    {
        try {
            $token = m::mock(Token::class)
                ->shouldReceive('isExpired')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getToken')
                ->times(1)
                ->andReturn($token)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByToken')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new TokenInUrlParameterExpired($userRepository);
            $validator->checkIfTokenInUrlParameterHasExpired(new ParameterBag(['token' => '1234567890']));
            $this->assertTrue(true);
        } catch (TokenExpiredException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfTokenInUrlParameterHasExpiredThrowsException(): void
    {
        try {
            $token = m::mock(Token::class)
                ->shouldReceive('isExpired')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getToken')
                ->times(1)
                ->andReturn($token)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByToken')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new TokenInUrlParameterExpired($userRepository);
            $validator->checkIfTokenInUrlParameterHasExpired(new ParameterBag(['token' => '1234567890']));
            $this->fail('Failed to throw exception.');
        } catch (TokenExpiredException $e) {
            $data = $e->getData();
            $this->assertSame(18, $data['error']['code']);
            $this->assertSame('Token has expired. New email was sent.', $data['error']['message']);
            $this->assertSame('Token has expired. New email was sent.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
