<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\DateTime\DateTimeUTC;
use App\EntityFactories\SecurityCodeFactory;
use App\Generators\SecurityCodeGenerator;
use DateTime;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SecurityCodeFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var SecurityCodeGenerator $securityCodeGenerator */
        $securityCodeGenerator = m::mock(SecurityCodeGenerator::class)
            ->shouldReceive('generateSecurityCode')
            ->times(1)
            ->andReturn('C50D9XAF6')
            ->getMock();

        $securityCodeFactory = new SecurityCodeFactory($securityCodeGenerator);

        $now = (new DateTimeUTC())->createDateTimeInstance();

        $securityCode = $securityCodeFactory->create($now);

        $this->assertSame('C50D9XAF6', $securityCode->getCode());
        $this->assertInstanceOf(DateTime::class, $securityCode->getCreatedAt());
        $this->assertSame(0, $securityCode->getInputFailures());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
