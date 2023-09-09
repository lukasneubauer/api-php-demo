<?php

declare(strict_types=1);

namespace Tests\App\Checks;

use App\Checks\ExpirationCheck;
use App\DateTime\DateTimeUTC;
use PHPUnit\Framework\TestCase;

final class ExpirationCheckTest extends TestCase
{
    /**
     * @dataProvider getParams
     */
    public function testIsUuidValid(string $timeOfCreation, int $expirationInSeconds, bool $isExpired): void
    {
        $this->assertSame(
            $isExpired,
            (new ExpirationCheck(new DateTimeUTC()))->isExpired(
                (new DateTimeUTC())->createDateTimeInstance($timeOfCreation),
                $expirationInSeconds
            )
        );
    }

    public static function getParams(): array
    {
        $tenMinutesInSeconds = 600;

        return [
            [
                '-12 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '-11 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '-10 min -1 sec',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '-10 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-9 min -59 sec',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-9 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-8 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-7 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-6 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-5 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-4 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-3 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-2 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '-1 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '0 min -1 sec',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '0 min',
                $tenMinutesInSeconds,
                false,
            ],
            [
                '0 min +1 sec',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+1 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+2 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+3 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+4 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+5 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+6 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+7 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+8 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+9 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+10 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+11 min',
                $tenMinutesInSeconds,
                true,
            ],
            [
                '+12 min',
                $tenMinutesInSeconds,
                true,
            ],
        ];
    }
}
