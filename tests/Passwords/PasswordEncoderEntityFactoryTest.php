<?php

declare(strict_types=1);

namespace Tests\App\Passwords;

use App\Entities\Password;
use App\EntityFactories\PasswordFactory;
use App\Exceptions\PasswordHashingFailedException;
use App\Exceptions\UnsupportedPasswordAlgorithmException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordEncoder;
use App\Passwords\PasswordEncoderEntityFactory;
use App\Passwords\PasswordSettings;
use PHPUnit\Framework\TestCase;

final class PasswordEncoderEntityFactoryTest extends TestCase
{
    /**
     * @dataProvider getData
     *
     * @throws PasswordHashingFailedException
     * @throws UnsupportedPasswordAlgorithmException
     */
    public function testCreatePassword(
        string $algorithm,
        array $options,
        string $startsWith,
        int $hashLength
    ): void {
        $passwordFactory = new PasswordFactory();
        $passwordSettings = new PasswordSettings(
            $algorithm,
            $options[0],
            $options[1],
            $options[2]
        );
        $passwordEncoder = new PasswordEncoder($passwordSettings);
        $passwordEncoderEntityFactory = new PasswordEncoderEntityFactory(
            $passwordEncoder,
            $passwordFactory,
            $passwordSettings
        );
        $password = $passwordEncoderEntityFactory->createPassword('secret');
        $this->assertInstanceOf(Password::class, $password);
        $this->assertTrue(\is_string($password->getHash()));
        $this->assertSame($hashLength, \strlen($password->getHash()));
        $this->assertStringStartsWith($startsWith, $password->getHash());
        $this->assertSame($algorithm, $password->getAlgorithm());
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
