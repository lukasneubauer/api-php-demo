<?php

declare(strict_types=1);

namespace Tests\App\Passwords;

use App\Exceptions\UnsupportedPasswordAlgorithmException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordSettings;
use PHPUnit\Framework\TestCase;

final class PasswordSettingsTest extends TestCase
{
    /**
     * @dataProvider getData
     *
     * @throws UnsupportedPasswordAlgorithmException
     */
    public function testConstruct(
        string $algorithm,
        string $algorithmPhpInternalConstantValue,
        array $options
    ): void {
        $passwordSettings = new PasswordSettings(
            $algorithm,
            $options['cost'],
            $options['time_cost'],
            $options['memory_cost']
        );
        $this->assertSame($algorithm, $passwordSettings->getAlgorithm());
        $this->assertSame($algorithmPhpInternalConstantValue, $passwordSettings->getAlgorithmPhpInternalConstantValue());
        $this->assertSame($options, $passwordSettings->getOptions());
    }

    public static function getData(): array
    {
        $options = [
            'cost' => 13,
            'time_cost' => 4,
            'memory_cost' => 65536,
            'threads' => 1,
        ];

        return [
            [
                PasswordAlgorithms::BCRYPT,
                \PASSWORD_BCRYPT,
                $options,
            ],
            [
                PasswordAlgorithms::ARGON2I,
                \PASSWORD_ARGON2I,
                $options,
            ],
            [
                PasswordAlgorithms::ARGON2ID,
                \PASSWORD_ARGON2ID,
                $options,
            ],
        ];
    }

    public function testConstructThrowsException(): void
    {
        try {
            new PasswordSettings(
                'INCORRECT_ALGORITHM',
                13,
                4,
                65536
            );
            $this->fail('Failed to throw exception.');
        } catch (UnsupportedPasswordAlgorithmException $e) {
            $this->assertSame('Unsupported password algorithm: INCORRECT_ALGORITHM.', $e->getMessage());
        }
    }
}
