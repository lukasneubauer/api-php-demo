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
class Token
{
    /** @var int */
    public const EXPIRATION_IN_SECONDS = 3600;

    /** @var int */
    public const LENGTH = 20;

    /** @var string */
    public const PATTERN = '0-9a-z';

    /** @ORM\Column(name="`token`", type="string", length=20, options={"fixed": true}, unique=true, nullable=true) */
    private ?string $code;

    /** @ORM\Column(name="`token_created_at`", type="datetime", nullable=true) */
    private ?DateTime $createdAt;

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

    public function isExpired(): bool
    {
        return (new ExpirationCheck(new DateTimeUTC()))->isExpired($this->getCreatedAt(), self::EXPIRATION_IN_SECONDS);
    }

    public function setEmpty(): self
    {
        $this->code = null;
        $this->createdAt = null;

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->code === null
            && $this->createdAt === null;
    }
}
