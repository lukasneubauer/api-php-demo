<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Session;
use App\Exceptions\ValidationException;
use App\Repositories\SessionRepository;
use App\Validators\InvalidValueForApiClientIdHttpHeader;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class InvalidValueForApiClientIdHttpHeaderTest extends TestCase
{
    public function testCheckIfApiClientIdIsInvalidDoesNotThrowException(): void
    {
        try {
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiClientId')
                ->times(1)
                ->andReturn(m::mock(Session::class))
                ->getMock();
            $validator = new InvalidValueForApiClientIdHttpHeader($sessionRepository);
            $validator->checkIfApiClientIdIsInvalid(new HeaderBag(['Api-Client-Id' => 'CLIENT-ID']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfApiClientIdIsInvalidThrowsException(): void
    {
        try {
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiClientId')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new InvalidValueForApiClientIdHttpHeader($sessionRepository);
            $validator->checkIfApiClientIdIsInvalid(new HeaderBag(['Api-Client-Id' => 'CLIENT-ID']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(3, $data['error']['code']);
            $this->assertSame("Invalid value for 'Api-Client-Id' http header.", $data['error']['message']);
            $this->assertSame("Invalid value for 'Api-Client-Id' http header.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
