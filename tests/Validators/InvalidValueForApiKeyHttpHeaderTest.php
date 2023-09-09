<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Http\ApiKey;
use App\Validators\InvalidValueForApiKeyHttpHeader;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class InvalidValueForApiKeyHttpHeaderTest extends TestCase
{
    public function testCheckIfApiKeyIsInvalidDoesNotThrowException(): void
    {
        try {
            $apiKey = m::mock(ApiKey::class)
                ->shouldReceive('getApiKey')
                ->times(1)
                ->andReturn('api-key')
                ->getMock();
            $validator = new InvalidValueForApiKeyHttpHeader($apiKey);
            $validator->checkIfApiKeyIsInvalid(new HeaderBag(['Api-Key' => 'api-key']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfApiKeyIsInvalidThrowsException(): void
    {
        try {
            $apiKey = m::mock(ApiKey::class)
                ->shouldReceive('getApiKey')
                ->times(1)
                ->andReturn('api-key')
                ->getMock();
            $validator = new InvalidValueForApiKeyHttpHeader($apiKey);
            $validator->checkIfApiKeyIsInvalid(new HeaderBag(['Api-Key' => 'INVALID_VALUE']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(3, $data['error']['code']);
            $this->assertSame("Invalid value for 'Api-Key' http header.", $data['error']['message']);
            $this->assertSame("Invalid value for 'Api-Key' http header.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
