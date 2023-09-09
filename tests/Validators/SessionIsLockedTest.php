<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Session;
use App\Exceptions\ValidationException;
use App\Repositories\SessionRepository;
use App\Validators\SessionIsLocked;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class SessionIsLockedTest extends TestCase
{
    public function testCheckIfSessionIsLockedDoesNotThrowException(): void
    {
        try {
            $session = m::mock(Session::class)
                ->shouldReceive('isLocked')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new SessionIsLocked($sessionRepository);
            $validator->checkIfSessionIsLocked(new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfSessionIsLockedThrowsException(): void
    {
        try {
            $session = m::mock(Session::class)
                ->shouldReceive('isLocked')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new SessionIsLocked($sessionRepository);
            $validator->checkIfSessionIsLocked(new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(39, $data['error']['code']);
            $this->assertSame('Session is locked. User must re-authenticate.', $data['error']['message']);
            $this->assertSame('Session is locked. User must re-authenticate.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
