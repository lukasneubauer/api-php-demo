<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class Password
{
    /** @ORM\Column(name="`password_hash`", type="string", nullable=true) */
    private ?string $hash;

    /** @ORM\Column(name="`password_algorithm`", type="string", nullable=true) */
    private ?string $algorithm;

    public function __construct(string $hash, string $algorithm)
    {
        $this->hash = $hash;
        $this->algorithm = $algorithm;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    public function setEmpty(): self
    {
        $this->hash = null;
        $this->algorithm = null;

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->hash === null
            && $this->algorithm === null;
    }
}
