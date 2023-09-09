<?php

declare(strict_types=1);

namespace Tests\App\Http;

use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiClientIdFoundException;
use App\Http\ApiClientId;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

final class ApiClientIdTest extends TestCase
{
    public function testGetApiClientIdFailsBecauseCouldNotGetCurrentRequestFromRequestStack(): void
    {
        try {
            $requestStack = m::mock(RequestStack::class)
                ->shouldReceive('getCurrentRequest')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $apiClientId = new ApiClientId($requestStack);
            $apiClientId->getApiClientId();
            $this->fail('Failed to throw exception.');
        } catch (CouldNotGetCurrentRequestFromRequestStackException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetApiClientIdFailsBecauseApiClientIdWasNotFound(): void
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
            $apiClientId = new ApiClientId($requestStack);
            $apiClientId->getApiClientId();
            $this->fail('Failed to throw exception.');
        } catch (NoApiClientIdFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetApiClientId(): void
    {
        try {
            $expectedResult = 'ritrykheck2gcju77nyjglwzp1k3bn3fd5hx5w2m';
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
            $apiClientId = new ApiClientId($requestStack);
            $result = $apiClientId->getApiClientId();
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
