<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\EntityFactories\PasswordFactory;
use App\Passwords\PasswordAlgorithms;
use PHPUnit\Framework\TestCase;

class PasswordFactoryTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testCreate(string $hash, string $algorithm): void
    {
        $passwordFactory = new PasswordFactory();
        $password = $passwordFactory->create($hash, $algorithm);
        $this->assertSame($hash, $password->getHash());
        $this->assertSame($algorithm, $password->getAlgorithm());
    }

    public static function getData(): array
    {
        return [
            [
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                PasswordAlgorithms::BCRYPT,
            ],
            [
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                PasswordAlgorithms::ARGON2I,
            ],
            [
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                PasswordAlgorithms::ARGON2ID,
            ],
        ];
    }
}
