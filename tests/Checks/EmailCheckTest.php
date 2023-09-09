<?php

declare(strict_types=1);

namespace Tests\App\Checks;

use App\Checks\EmailCheck;
use PHPUnit\Framework\TestCase;

final class EmailCheckTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testIsEmailInValidFormat(string $email, bool $expectedEmailValidity): void
    {
        $emailCheck = new EmailCheck();
        $isEmailValid = $emailCheck->isEmailInValidFormat($email);
        $this->assertSame($expectedEmailValidity, $isEmailValid);
    }

    public static function getData(): array
    {
        return [
            [
                ' @b.c',
                true,
            ],
            [
                '-@b.c',
                true,
            ],
            [
                'a@b.c',
                true,
            ],
            [
                'A@B.C',
                true,
            ],
            [
                '1@2.3',
                true,
            ],
            [
                'john.doe@example.com',
                true,
            ],
            [
                'John.Doe@example.com',
                true,
            ],
            [
                'john.doe@subdomain.example.com',
                true,
            ],
            [
                'john.doe.example.com',
                false,
            ],
            [
                'john.doe@example_com',
                false,
            ],
        ];
    }
}
