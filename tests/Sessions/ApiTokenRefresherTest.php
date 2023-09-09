<?php

declare(strict_types=1);

namespace Tests\App\Sessions;

use App\DateTime\DateTimeUTC;
use App\DateTime\DateTimeUTCFromTimestamp;
use App\Entities\Session;
use App\Entities\User;
use App\Generators\ApiTokenGenerator;
use App\Sessions\ApiTokenRefresher;
use Doctrine\ORM\EntityManager;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class ApiTokenRefresherTest extends TestCase
{
    /**
     * @dataProvider getTimestampsThatAreNotExpiredYet
     */
    public function testRefreshApiTokenIfExpiredNotExpiredYet(int $expiration): void
    {
        $nowTs = \time();

        $apiTokenGenerator = m::mock(ApiTokenGenerator::class);

        $dateTimeUTC = m::mock(DateTimeUTC::class)
            ->shouldReceive('createDateTimeInstance')
            ->times(1)
            ->andReturn((new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs))
            ->getMock();

        $em = m::mock(EntityManager::class);

        $user = m::mock(User::class);

        $sessionRefreshedAt = (new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs - $expiration);
        $sessionUpdatedAt = (new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs - $expiration);

        $session = new Session(
            '8949eaf5-d4bc-4cc8-964d-6e5156da43e5',
            $user,
            'qdq4grz42wk93xgbxi4jrpeuxt82jpxbuy376iqs',
            'shpahz907d446db09fk1firfywnphj63lnvce6tw5ovt1hryikitff66e24jx528lajnwwpos8plg4ud',
            $sessionRefreshedAt,
            (new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs - $expiration),
            $sessionUpdatedAt
        );

        $apiTokenRefresher = new ApiTokenRefresher($apiTokenGenerator, $dateTimeUTC, $em);
        $refreshedSession = $apiTokenRefresher->refreshApiTokenIfExpired($session);

        $this->assertNull($refreshedSession->getOldApiToken());
        $this->assertSame('shpahz907d446db09fk1firfywnphj63lnvce6tw5ovt1hryikitff66e24jx528lajnwwpos8plg4ud', $refreshedSession->getCurrentApiToken());
        $this->assertSame(
            $sessionRefreshedAt->getTimestamp(),
            $refreshedSession->getRefreshedAt()->getTimestamp()
        );
        $this->assertSame(
            $sessionUpdatedAt->getTimestamp(),
            $refreshedSession->getUpdatedAt()->getTimestamp()
        );
        $this->assertSame(
            $refreshedSession->getRefreshedAt()->getTimestamp(),
            $refreshedSession->getUpdatedAt()->getTimestamp()
        );
    }

    public static function getTimestampsThatAreNotExpiredYet(): array
    {
        return [
            [0],
            [899],
        ];
    }

    /**
     * @dataProvider getTimestampsThatAreAlreadyExpired
     */
    public function testRefreshApiTokenIfExpiredAlreadyExpired(int $expiration): void
    {
        $nowTs = \time();

        $apiTokenGenerator = m::mock(ApiTokenGenerator::class)
            ->shouldReceive('generateApiToken')
            ->times(1)
            ->andReturn('us8cjyppp38921fz2mgjoolj4ji06fr2lxytl2h8r08wz75qb0q2exszbxrcbdf3cuek0z8g5hmbh2x5')
            ->getMock();

        $dateTimeUTC = m::mock(DateTimeUTC::class)
            ->shouldReceive('createDateTimeInstance')
            ->times(1)
            ->andReturn((new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs))
            ->getMock();

        $em = m::mock(EntityManager::class);
        $em->shouldReceive('persist')
            ->times(1)
            ->andReturnUndefined();
        $em->shouldReceive('flush')
            ->times(1)
            ->andReturnUndefined();

        $user = m::mock(User::class);

        $sessionRefreshedAt = (new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs - $expiration);
        $sessionUpdatedAt = (new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs - $expiration);

        $session = new Session(
            '8949eaf5-d4bc-4cc8-964d-6e5156da43e5',
            $user,
            'qdq4grz42wk93xgbxi4jrpeuxt82jpxbuy376iqs',
            'shpahz907d446db09fk1firfywnphj63lnvce6tw5ovt1hryikitff66e24jx528lajnwwpos8plg4ud',
            $sessionRefreshedAt,
            (new DateTimeUTCFromTimestamp())->createDateTimeInstanceFromTimestamp($nowTs - $expiration),
            $sessionUpdatedAt
        );

        $apiTokenRefresher = new ApiTokenRefresher($apiTokenGenerator, $dateTimeUTC, $em);
        $refreshedSession = $apiTokenRefresher->refreshApiTokenIfExpired($session);

        $this->assertSame('shpahz907d446db09fk1firfywnphj63lnvce6tw5ovt1hryikitff66e24jx528lajnwwpos8plg4ud', $refreshedSession->getOldApiToken());
        $this->assertSame('us8cjyppp38921fz2mgjoolj4ji06fr2lxytl2h8r08wz75qb0q2exszbxrcbdf3cuek0z8g5hmbh2x5', $refreshedSession->getCurrentApiToken());
        $this->assertGreaterThan(
            $sessionRefreshedAt->getTimestamp(),
            $refreshedSession->getRefreshedAt()->getTimestamp()
        );
        $this->assertGreaterThan(
            $sessionUpdatedAt->getTimestamp(),
            $refreshedSession->getUpdatedAt()->getTimestamp()
        );
        $this->assertSame(
            $refreshedSession->getRefreshedAt()->getTimestamp(),
            $refreshedSession->getUpdatedAt()->getTimestamp()
        );
    }

    public static function getTimestampsThatAreAlreadyExpired(): array
    {
        return [
            [900],
            [901],
        ];
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
