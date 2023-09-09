<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Session;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Repositories\SessionRepository;
use App\Validators\SessionFoundByApiTokenButItsClientIdDoesNotMatch;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class SessionFoundByApiTokenButItsClientIdDoesNotMatchTest extends TestCase
{
    public function testCheckIfSessionFoundByApiTokenButItsClientIdDoesNotMatchDoesNotThrowException(): void
    {
        try {
            $session = m::mock(Session::class)
                ->shouldReceive('getApiClientId')
                ->times(1)
                ->andReturn('CLIENT-ID')
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new SessionFoundByApiTokenButItsClientIdDoesNotMatch($sessionRepository);
            $validator->checkIfSessionFoundByApiTokenButItsClientIdDoesNotMatch(new HeaderBag([
                'Api-Client-Id' => 'CLIENT-ID',
                'Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573',
            ]));
            $this->assertTrue(true);
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfSessionFoundByApiTokenButItsClientIdDoesNotMatchThrowsException(): void
    {
        try {
            $session = m::mock(Session::class)
                ->shouldReceive('getApiClientId')
                ->times(1)
                ->andReturn('CLIENT-ID')
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new SessionFoundByApiTokenButItsClientIdDoesNotMatch($sessionRepository);
            $validator->checkIfSessionFoundByApiTokenButItsClientIdDoesNotMatch(new HeaderBag([
                'Api-Client-Id' => 'NOT-MATCHING-CLIENT-ID',
                'Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573',
            ]));
            $this->fail('Failed to throw exception.');
        } catch (SessionHasNotMatchingClientIdException $e) {
            $data = $e->getData();
            $this->assertSame(38, $data['error']['code']);
            $this->assertSame('Session found by api token but its client id does not match with the one provided in header Api-Client-Id. Session has been locked for security reasons.', $data['error']['message']);
            $this->assertSame('Session found by api token but its client id does not match with the one provided in header Api-Client-Id. Session has been locked for security reasons.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
