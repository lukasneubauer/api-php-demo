<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Session;
use App\Exceptions\ValidationException;
use App\Repositories\SessionRepository;
use App\Validators\OldApiClientIdIsDifferentThanTheOneInCurrentSession;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class OldApiClientIdIsDifferentThanTheOneInCurrentSessionTest extends TestCase
{
    public function testCheckIfOldApiClientIdIsDifferentThanTheOneInCurrentSessionDoesNotThrowException(): void
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
            $validator = new OldApiClientIdIsDifferentThanTheOneInCurrentSession($sessionRepository);
            $validator->checkIfOldApiClientIdIsDifferentThanTheOneInCurrentSession(
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

    public function testCheckIfOldApiClientIdIsDifferentThanTheOneInCurrentSessionThrowsException(): void
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
            $validator = new OldApiClientIdIsDifferentThanTheOneInCurrentSession($sessionRepository);
            $validator->checkIfOldApiClientIdIsDifferentThanTheOneInCurrentSession(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                    'oldApiClientId' => 'INVALID-CLIENT-ID',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(47, $data['error']['code']);
            $this->assertSame('Re-authentication failed. Value of old api client id in request body is different than the one in current session.', $data['error']['message']);
            $this->assertSame('Re-authentication failed. Value of old api client id in request body is different than the one in current session.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
