<?php

declare(strict_types=1);

namespace Tests\App\Sessions;

use App\Entities\Session;
use App\Exceptions\SessionNotFoundByApiTokenException;
use App\Repositories\SessionRepository;
use App\Sessions\SessionLocator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class SessionLocatorTest extends TestCase
{
    public function testLocateSession(): void
    {
        $sessionRepository = m::mock(SessionRepository::class)
            ->shouldReceive('getByApiToken')
            ->times(1)
            ->andReturn(m::mock(Session::class))
            ->getMock();
        $sessionLocator = new SessionLocator($sessionRepository);
        $session = $sessionLocator->locateSession('1234567890');
        $this->assertInstanceOf(Session::class, $session);
    }

    public function testLocateSessionThrowsException(): void
    {
        try {
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $sessionLocator = new SessionLocator($sessionRepository);
            $sessionLocator->locateSession('1234567890');
            $this->fail('Failed to throw exception.');
        } catch (SessionNotFoundByApiTokenException $e) {
            $this->assertSame("Session not found by api token '1234567890'.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
