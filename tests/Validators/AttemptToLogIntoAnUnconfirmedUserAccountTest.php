<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\AttemptToLogIntoAnUnconfirmedUserAccount;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class AttemptToLogIntoAnUnconfirmedUserAccountTest extends TestCase
{
    public function testCheckIfUserIsAttemptingToLogIntoAnUnconfirmedAccountDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('isActive')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new AttemptToLogIntoAnUnconfirmedUserAccount($userRepository);
            $validator->checkIfUserIsAttemptingToLogIntoAnUnconfirmedAccount(['email' => 'john.doe@example.com']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserIsAttemptingToLogIntoAnUnconfirmedAccountThrowsExceptionBecauseOfInvalidToken(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('isActive')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new AttemptToLogIntoAnUnconfirmedUserAccount($userRepository);
            $validator->checkIfUserIsAttemptingToLogIntoAnUnconfirmedAccount(['email' => 'john.doe@example.com']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(34, $data['error']['code']);
            $this->assertSame('Attempt to log into an unconfirmed user account.', $data['error']['message']);
            $this->assertSame('Attempt to log into an unconfirmed user account.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
