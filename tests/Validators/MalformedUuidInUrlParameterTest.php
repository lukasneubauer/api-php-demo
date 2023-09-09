<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Checks\UuidCheck;
use App\Exceptions\ValidationException;
use App\Validators\MalformedUuidInUrlParameter;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\MalformedUuidDataProvider;

final class MalformedUuidInUrlParameterTest extends TestCase
{
    public function testCheckIfUuidIsMalformedDoesNotThrowException(): void
    {
        try {
            $uuidCheck = m::mock(UuidCheck::class)
                ->shouldReceive('isUuidValid')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $validator = new MalformedUuidInUrlParameter($uuidCheck, 'id');
            $validator->checkIfUuidIsMalformed(new ParameterBag(['id' => 'ac666690-53ca-4980-969a-04d5009cf9a5']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getMalformedUuids
     */
    public function testCheckIfUuidIsMalformedThrowsException(string $uuid): void
    {
        try {
            $uuidCheck = m::mock(UuidCheck::class)
                ->shouldReceive('isUuidValid')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $validator = new MalformedUuidInUrlParameter($uuidCheck, 'id');
            $validator->checkIfUuidIsMalformed(new ParameterBag(['id' => $uuid]));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(19, $data['error']['code']);
            $this->assertSame('Malformed uuid.', $data['error']['message']);
            $this->assertSame('Malformed uuid.', $e->getMessage());
        }
    }

    public static function getMalformedUuids(): array
    {
        return MalformedUuidDataProvider::getMalformedUuids();
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
