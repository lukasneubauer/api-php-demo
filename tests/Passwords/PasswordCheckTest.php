<?php

declare(strict_types=1);

namespace Tests\App\Passwords;

use App\Passwords\PasswordCheck;
use PHPUnit\Framework\TestCase;

final class PasswordCheckTest extends TestCase
{
    /**
     * @dataProvider getPasswords
     */
    public function testIsPasswordCorrect(string $plainPassword, string $hash, bool $expectedReturnValue): void
    {
        $passwordCheck = new PasswordCheck();
        $isCorrect = $passwordCheck->isPasswordCorrect($plainPassword, $hash);
        $this->assertSame($expectedReturnValue, $isCorrect);
    }

    public static function getPasswords(): array
    {
        return [
            [
                'secret',
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                true,
            ],
            [
                'incorrect-password',
                '$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi',
                false,
            ],
            [
                'secret',
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                true,
            ],
            [
                'incorrect-password',
                '$argon2i$v=19$m=65536,t=4,p=1$VXJvZzlqeWJuQ0xQWll0aw$u3/LlN7qqnHWki0myRYElgzSDezvBMC5ouALU3CBpjc',
                false,
            ],
            [
                'secret',
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                true,
            ],
            [
                'incorrect-password',
                '$argon2id$v=19$m=65536,t=4,p=1$qdOmqr3PQVUfeQJdoHORlg$Yr7hdBqlgz2t/+xOCXAqQl4WK3yeZxsBpWYL/6fAa4Q',
                false,
            ],
        ];
    }
}
