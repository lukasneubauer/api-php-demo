<?php

declare(strict_types=1);

namespace App\Passwords;

use App\Exceptions\UnsupportedPasswordAlgorithmException;

class PasswordSettings
{
    private string $algorithm;

    private string $algorithmPhpInternalConstantValue;

    private array $options;

    /**
     * @throws UnsupportedPasswordAlgorithmException
     */
    public function __construct(
        string $algorithm,
        int $bcryptCost,
        int $argonTimeCost,
        int $argonMemoryCost
    ) {
        $this->algorithm = $algorithm;

        if ($this->algorithm === PasswordAlgorithms::BCRYPT) {
            $this->algorithmPhpInternalConstantValue = \PASSWORD_BCRYPT;
        } elseif ($this->algorithm === PasswordAlgorithms::ARGON2I) {
            $this->algorithmPhpInternalConstantValue = \PASSWORD_ARGON2I;
        } elseif ($this->algorithm === PasswordAlgorithms::ARGON2ID) {
            $this->algorithmPhpInternalConstantValue = \PASSWORD_ARGON2ID;
        } else {
            throw new UnsupportedPasswordAlgorithmException(
                \sprintf('Unsupported password algorithm: %s.', $algorithm)
            );
        }

        $this->options = [
            'cost' => $bcryptCost,
            'time_cost' => $argonTimeCost,
            'memory_cost' => $argonMemoryCost,
            'threads' => 1,
        ];
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    public function getAlgorithmPhpInternalConstantValue(): string
    {
        return $this->algorithmPhpInternalConstantValue;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
