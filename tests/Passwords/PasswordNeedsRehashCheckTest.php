<?php

declare(strict_types=1);

namespace Tests\App\Passwords;

use App\Exceptions\UnsupportedPasswordAlgorithmException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordNeedsRehashCheck;
use App\Passwords\PasswordSettings;
use PHPUnit\Framework\TestCase;

final class PasswordNeedsRehashCheckTest extends TestCase
{
    /**
     * @dataProvider getData
     *
     * @throws UnsupportedPasswordAlgorithmException
     */
    public function testDoesPasswordNeedRehash(
        string $hash,
        string $algorithm,
        array $options,
        bool $needsRehash
    ): void {
        $passwordSettings = new PasswordSettings(
            $algorithm,
            $options[0],
            $options[1],
            $options[2]
        );
        $passwordNeedsRehashCheck = new PasswordNeedsRehashCheck($passwordSettings);
        $passwordNeedsRehash = $passwordNeedsRehashCheck->doesPasswordNeedRehash($hash);
        $this->assertSame($needsRehash, $passwordNeedsRehash);
    }

    public static function getData(): array
    {
        return [
            [
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                PasswordAlgorithms::BCRYPT,
                [
                    13,
                    4,
                    65536,
                ],
                false,
            ],
            [
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                PasswordAlgorithms::BCRYPT,
                [
                    12,
                    4,
                    65536,
                ],
                true,
            ],
            [
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    4,
                    65536,
                ],
                true,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    4,
                    65536,
                ],
                false,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    3,
                    65536,
                ],
                true,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    4,
                    32768,
                ],
                true,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
                [
                    13,
                    3,
                    32768,
                ],
                true,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    4,
                    65536,
                ],
                true,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    4,
                    65536,
                ],
                false,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    3,
                    65536,
                ],
                true,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    4,
                    32768,
                ],
                true,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
                [
                    13,
                    3,
                    32768,
                ],
                true,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::BCRYPT,
                [
                    13,
                    4,
                    65536,
                ],
                true,
            ],
        ];
    }
}
