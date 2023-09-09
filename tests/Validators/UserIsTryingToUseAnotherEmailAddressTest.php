<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Session;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\SessionRepository;
use App\Validators\UserIsTryingToUseAnotherEmailAddress;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class UserIsTryingToUseAnotherEmailAddressTest extends TestCase
{
    public function testCheckIfUserIsTryingToUseAnotherEmailAddressDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('getEmail')
                ->times(1)
                ->andReturn('john.doe@example.com')
                ->getMock();
            $session = m::mock(Session::class)
                ->shouldReceive('getUser')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new UserIsTryingToUseAnotherEmailAddress($sessionRepository);
            $validator->checkIfUserIsTryingToUseAnotherEmailAddress(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                    'oldApiClientId' => 'CLIENT-ID',
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserIsTryingToUseAnotherEmailAddressThrowsException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('getEmail')
                ->times(1)
                ->andReturn('john.doe@example.com')
                ->getMock();
            $session = m::mock(Session::class)
                ->shouldReceive('getUser')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new UserIsTryingToUseAnotherEmailAddress($sessionRepository);
            $validator->checkIfUserIsTryingToUseAnotherEmailAddress(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'email' => 'someone-elses-email@example.com',
                    'password' => 'secret',
                    'oldApiClientId' => 'CLIENT-ID',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(46, $data['error']['code']);
            $this->assertSame("Re-authentication failed. User is trying to use another user's email address.", $data['error']['message']);
            $this->assertSame("Re-authentication failed. User is trying to use another user's email address.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
