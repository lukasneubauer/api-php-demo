<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\EntityFactories\SessionFactory;
use App\Generators\ApiTokenGenerator;
use App\Generators\UuidGenerator;
use DateTime;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SessionFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = m::mock(UuidGenerator::class)
            ->shouldReceive('generateUuid')
            ->times(1)
            ->andReturn('24887dd6-df68-4938-a4a1-49c6401a0389')
            ->getMock();

        /** @var ApiTokenGenerator $apiTokenGenerator */
        $apiTokenGenerator = m::mock(ApiTokenGenerator::class)
            ->shouldReceive('generateApiToken')
            ->times(1)
            ->andReturn('gm2qbo34dn35fusjgs3imtpuc2sjvwr5co9vijyu')
            ->getMock();

        $sessionFactory = new SessionFactory($uuidGenerator, $apiTokenGenerator);

        /** @var User $user */
        $user = m::mock(User::class);

        $now = (new DateTimeUTC())->createDateTimeInstance();

        $session = $sessionFactory->create(
            $user,
            '5selt5w7vr7ccgmj6yfajgug2g218rhd01al6euo',
            $now
        );

        $this->assertSame('24887dd6-df68-4938-a4a1-49c6401a0389', $session->getId());
        $this->assertInstanceOf(User::class, $session->getUser());
        $this->assertSame('5selt5w7vr7ccgmj6yfajgug2g218rhd01al6euo', $session->getApiClientId());
        $this->assertNull($session->getOldApiToken());
        $this->assertSame('gm2qbo34dn35fusjgs3imtpuc2sjvwr5co9vijyu', $session->getCurrentApiToken());
        $this->assertInstanceOf(DateTime::class, $session->getRefreshedAt());
        $this->assertFalse($session->isLocked());
        $this->assertInstanceOf(DateTime::class, $session->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $session->getUpdatedAt());
        $this->assertSame($session->getCreatedAt()->getTimestamp(), $session->getRefreshedAt()->getTimestamp());
        $this->assertSame($session->getCreatedAt()->getTimestamp(), $session->getUpdatedAt()->getTimestamp());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
