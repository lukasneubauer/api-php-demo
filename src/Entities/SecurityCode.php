<?php

declare(strict_types=1);

namespace App\Entities;

use App\Checks\ExpirationCheck;
use App\DateTime\DateTimeUTC;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class SecurityCode
{
    /** @var int */
    public const EXPIRATION_IN_SECONDS = 300;

    /** @var int */
    public const LENGTH = 9;

    /** @var string */
    public const PATTERN = '0-9A-Z';

    /** @var int */
    public const MAX_INPUT_FAILURE_ATTEMPTS = 3;

    /** @ORM\Column(name="`security_code`", type="string", length=9, options={"fixed": true}, nullable=true) */
    private ?string $code;

    /** @ORM\Column(name="`security_code_created_at`", type="datetime", nullable=true) */
    private ?DateTime $createdAt;

    /** @ORM\Column(name="`security_code_failures`", type="smallint", options={"unsigned": true}) */
    private int $inputFailures = 0;

    public function __construct(string $code, DateTime $createdAt)
    {
        $this->code = $code;
        $this->createdAt = $createdAt;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        if ($this->createdAt !== null) {
            return (new DateTimeUTC())->createDateTimeInstance($this->createdAt->format('Y-m-d H:i:s'));
        }

        return null;
    }

    public function setInputFailures(int $inputFailures): self
    {
        $this->inputFailures = $inputFailures;

        return $this;
    }

    public function getInputFailures(): int
    {
        return $this->inputFailures;
    }

    public function getInputFailuresLeft(): int
    {
        return self::MAX_INPUT_FAILURE_ATTEMPTS - $this->inputFailures;
    }

    public function isExpired(): bool
    {
        return (new ExpirationCheck(new DateTimeUTC()))->isExpired($this->getCreatedAt(), self::EXPIRATION_IN_SECONDS);
    }

    public function setEmpty(): self
    {
        $this->code = null;
        $this->createdAt = null;
        $this->inputFailures = 0;

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->code === null
            && $this->createdAt === null
            && $this->inputFailures === 0;
    }
}
