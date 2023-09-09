<?php

declare(strict_types=1);

namespace Tests\App\Http;

use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiToken;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

final class ApiTokenTest extends TestCase
{
    public function testGetApiTokenFailsBecauseCouldNotGetCurrentRequestFromRequestStack(): void
    {
        try {
            $requestStack = m::mock(RequestStack::class)
                ->shouldReceive('getCurrentRequest')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $apiToken = new ApiToken($requestStack);
            $apiToken->getApiToken();
            $this->fail('Failed to throw exception.');
        } catch (CouldNotGetCurrentRequestFromRequestStackException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetApiTokenFailsBecauseApiTokenWasNotFound(): void
    {
        try {
            $headers = m::mock(HeaderBag::class)
                ->shouldReceive('get')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $request = m::mock(Request::class);
            $request->headers = $headers;
            $requestStack = m::mock(RequestStack::class)
                ->shouldReceive('getCurrentRequest')
                ->times(1)
                ->andReturn($request)
                ->getMock();
            $apiToken = new ApiToken($requestStack);
            $apiToken->getApiToken();
            $this->fail('Failed to throw exception.');
        } catch (NoApiTokenFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetApiToken(): void
    {
        try {
            $expectedResult = 'ryc6v4md51svt84j92yneanv0p39b2vbya8dl1a51stiwcme51gokfx5sd8e9vxfuf0zcpjyqdlp5rmk';
            $headers = m::mock(HeaderBag::class)
                ->shouldReceive('get')
                ->times(1)
                ->andReturn($expectedResult)
                ->getMock();
            $request = m::mock(Request::class);
            $request->headers = $headers;
            $requestStack = m::mock(RequestStack::class)
                ->shouldReceive('getCurrentRequest')
                ->times(1)
                ->andReturn($request)
                ->getMock();
            $apiToken = new ApiToken($requestStack);
            $result = $apiToken->getApiToken();
            $this->assertSame($expectedResult, $result);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
