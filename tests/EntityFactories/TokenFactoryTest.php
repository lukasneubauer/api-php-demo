<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\DateTime\DateTimeUTC;
use App\EntityFactories\TokenFactory;
use App\Generators\TokenGenerator;
use DateTime;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TokenFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var TokenGenerator $tokenGenerator */
        $tokenGenerator = m::mock(TokenGenerator::class)
            ->shouldReceive('generateToken')
            ->times(1)
            ->andReturn('x7tdnxh01w03oz9tfua3')
            ->getMock();

        $tokenFactory = new TokenFactory($tokenGenerator);

        $now = (new DateTimeUTC())->createDateTimeInstance();

        $token = $tokenFactory->create($now);

        $this->assertSame('x7tdnxh01w03oz9tfua3', $token->getCode());
        $this->assertInstanceOf(DateTime::class, $token->getCreatedAt());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
