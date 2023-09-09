<?php

declare(strict_types=1);

namespace Tests\App\Passwords;

use App\Exceptions\PasswordHashingFailedException;
use App\Exceptions\UnsupportedPasswordAlgorithmException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordEncoder;
use App\Passwords\PasswordSettings;
use PHPUnit\Framework\TestCase;

final class PasswordEncoderTest extends TestCase
{
    /**
     * @dataProvider getData
     *
     * @throws PasswordHashingFailedException
     * @throws UnsupportedPasswordAlgorithmException
     */
    public function testHashPassword(
        string $algorithm,
        array $options,
        string $startsWith,
        int $hashLength
    ): void {
        $passwordSettings = new PasswordSettings(
            $algorithm,
            $options[0],
            $options[1],
            $options[2]
        );
        $passwordEncoder = new PasswordEncoder($passwordSettings);
        $hash = $passwordEncoder->hashPassword('secret');
        $this->assertSame($hashLength, \strlen($hash));
        $this->assertStringStartsWith($startsWith, $hash);
    }

    public static function getData(): array
    {
        $options = [
            13,
            4,
            65536,
        ];

        return [
            [
                PasswordAlgorithms::BCRYPT,
                $options,
                '$2y$13$',
                60,
            ],
            [
                PasswordAlgorithms::ARGON2I,
                $options,
                '$argon2i$v=19$m=65536,t=4,p=1$',
                96,
            ],
            [
                PasswordAlgorithms::ARGON2ID,
                $options,
                '$argon2id$v=19$m=65536,t=4,p=1$',
                97,
            ],
        ];
    }
}
