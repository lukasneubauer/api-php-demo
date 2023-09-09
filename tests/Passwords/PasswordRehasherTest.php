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
use App\Passwords\PasswordNeedsRehashCheck;
use App\Passwords\PasswordRehasher;
use App\Passwords\PasswordSettings;
use PHPUnit\Framework\TestCase;

final class PasswordRehasherTest extends TestCase
{
    /**
     * @dataProvider getDataForTestRehashPasswordNeedsRehash
     *
     * @throws PasswordHashingFailedException
     * @throws UnsupportedPasswordAlgorithmException
     */
    public function testRehashPasswordNeedsRehash(
        string $hash,
        string $activeAlgorithm,
        string $algorithm,
        array $options,
        string $startsWith,
        int $hashLength
    ): void {
        $passwordSettings = new PasswordSettings(
            $activeAlgorithm,
            $options[0],
            $options[1],
            $options[2]
        );
        $passwordEncoder = new PasswordEncoder($passwordSettings);
        $passwordFactory = new PasswordFactory();
        $passwordNeedsRehashCheck = new PasswordNeedsRehashCheck($passwordSettings);
        $passwordEncoderEntityFactory = new PasswordEncoderEntityFactory(
            $passwordEncoder,
            $passwordFactory,
            $passwordSettings
        );
        $passwordRehasher = new PasswordRehasher(
            $passwordNeedsRehashCheck,
            $passwordEncoderEntityFactory
        );
        $password = new Password($hash, $algorithm);
        $rehashedPassword = $passwordRehasher->rehashPassword('secret', $password);
        $this->assertNotSame($hash, $rehashedPassword->getHash());
        $this->assertSame($hashLength, \strlen($rehashedPassword->getHash()));
        $this->assertStringStartsWith($startsWith, $rehashedPassword->getHash());
        $this->assertSame($activeAlgorithm, $rehashedPassword->getAlgorithm());
    }

    public static function getDataForTestRehashPasswordNeedsRehash(): array
    {
        return [
            [
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                PasswordAlgorithms::BCRYPT,
                PasswordAlgorithms::BCRYPT,
                [
                    12,
                    4,
                    65536,
                ],
                '$2y$12$',
                60,
            ],
            [
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                PasswordAlgorithms::ARGON2I,
                PasswordAlgorithms::BCRYPT,
                [
                    13,
                    4,
                    65536,
                ],
                '$argon2i$v=19$m=65536,t=4,p=1$',
                96,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    3,
                    65536,
                ],
                '$argon2i$v=19$m=65536,t=3,p=1$',
                96,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    4,
                    32768,
                ],
                '$argon2i$v=19$m=32768,t=4,p=1$',
                96,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    3,
                    32768,
                ],
                '$argon2i$v=19$m=32768,t=3,p=1$',
                96,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2ID,
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    4,
                    65536,
                ],
                '$argon2id$v=19$m=65536,t=4,p=1$',
                97,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    3,
                    65536,
                ],
                '$argon2id$v=19$m=65536,t=3,p=1$',
                97,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    4,
                    32768,
                ],
                '$argon2id$v=19$m=32768,t=4,p=1$',
                97,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    3,
                    32768,
                ],
                '$argon2id$v=19$m=32768,t=3,p=1$',
                97,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::BCRYPT,
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    4,
                    65536,
                ],
                '$2y$13$',
                60,
            ],
        ];
    }

    /**
     * @dataProvider getDataForTestRehashPasswordDoesNotNeedRehash
     *
     * @throws PasswordHashingFailedException
     * @throws UnsupportedPasswordAlgorithmException
     */
    public function testRehashPasswordDoesNotNeedRehash(
        string $hash,
        string $activeAlgorithm,
        string $algorithm,
        array $options,
        string $startsWith,
        int $hashLength
    ): void {
        $passwordSettings = new PasswordSettings(
            $activeAlgorithm,
            $options[0],
            $options[1],
            $options[2]
        );
        $passwordEncoder = new PasswordEncoder($passwordSettings);
        $passwordFactory = new PasswordFactory();
        $passwordNeedsRehashCheck = new PasswordNeedsRehashCheck($passwordSettings);
        $passwordEncoderEntityFactory = new PasswordEncoderEntityFactory(
            $passwordEncoder,
            $passwordFactory,
            $passwordSettings
        );
        $passwordRehasher = new PasswordRehasher(
            $passwordNeedsRehashCheck,
            $passwordEncoderEntityFactory
        );
        $password = new Password($hash, $algorithm);
        $rehashedPassword = $passwordRehasher->rehashPassword('secret', $password);
        $this->assertSame($hash, $rehashedPassword->getHash());
        $this->assertSame($hashLength, \strlen($rehashedPassword->getHash()));
        $this->assertStringStartsWith($startsWith, $rehashedPassword->getHash());
        $this->assertSame($activeAlgorithm, $rehashedPassword->getAlgorithm());
    }

    public static function getDataForTestRehashPasswordDoesNotNeedRehash(): array
    {
        return [
            [
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                PasswordAlgorithms::BCRYPT,
                PasswordAlgorithms::BCRYPT,
                [
                    13,
                    4,
                    65536,
                ],
                '$2y$13$',
                60,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    4,
                    65536,
                ],
                '$argon2i$v=19$m=65536,t=4,p=1$',
                96,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    4,
                    65536,
                ],
                '$argon2id$v=19$m=65536,t=4,p=1$',
                97,
            ],
        ];
    }
}
