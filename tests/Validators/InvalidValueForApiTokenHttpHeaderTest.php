<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Session;
use App\Exceptions\ValidationException;
use App\Repositories\SessionRepository;
use App\Validators\InvalidValueForApiTokenHttpHeader;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class InvalidValueForApiTokenHttpHeaderTest extends TestCase
{
    public function testCheckIfApiTokenIsInvalidDoesNotThrowException(): void
    {
        try {
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn(m::mock(Session::class))
                ->getMock();
            $validator = new InvalidValueForApiTokenHttpHeader($sessionRepository);
            $validator->checkIfApiTokenIsInvalid(new HeaderBag(['Api-Token' => '1234567890']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfApiTokenIsInvalidThrowsException(): void
    {
        try {
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new InvalidValueForApiTokenHttpHeader($sessionRepository);
            $validator->checkIfApiTokenIsInvalid(new HeaderBag(['Api-Token' => '1234567890']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(3, $data['error']['code']);
            $this->assertSame("Invalid value for 'Api-Token' http header.", $data['error']['message']);
            $this->assertSame("Invalid value for 'Api-Token' http header.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
