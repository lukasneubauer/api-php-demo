<?php

declare(strict_types=1);

namespace Tests\App\Sessions;

use App\DateTime\DateTimeUTC;
use App\Entities\Session;
use App\Entities\User;
use App\Sessions\SessionLocator;
use App\Sessions\SessionLock;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class SessionLockTest extends TestCase
{
    public function testLockSession(): void
    {
        $now = (new DateTimeUTC())->createDateTimeInstance();
        $session = new Session(
            '',
            m::mock(User::class),
            '',
            '',
            $now,
            $now,
            $now
        );
        $session->setIsLocked(false);
        $sessionLocator = m::mock(SessionLocator::class)
            ->shouldReceive('locateSession')
            ->times(1)
            ->andReturn($session)
            ->getMock();
        $dateTimeUTC = m::mock(DateTimeUTC::class)
            ->shouldReceive('createDateTimeInstance')
            ->times(1)
            ->andReturn((new DateTimeUTC())->createDateTimeInstance())
            ->getMock();
        $sessionLock = new SessionLock($sessionLocator, $dateTimeUTC);
        $lockedSession = $sessionLock->lockSession('1234567890');
        $this->assertTrue($lockedSession->isLocked());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
