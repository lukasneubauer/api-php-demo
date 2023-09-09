<?php

declare(strict_types=1);

namespace App\Entities;

use App\DateTime\DateTimeUTC;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="`sessions`")
 * @ORM\Entity
 */
class Session
{
    use Id;
    use Timestamp;

    /** @var int */
    public const OLD_API_TOKEN_EXPIRATION_IN_SECONDS = 300;

    /** @var int */
    public const CURRENT_API_TOKEN_EXPIRATION_IN_SECONDS = 900;

    /** @var string */
    public const UNIQUE_KEY_OLD_API_TOKEN = 'UNIQ_9A609D1350661F4D';

    /** @var string */
    public const UNIQUE_KEY_CURRENT_API_TOKEN = 'UNIQ_9A609D13A13742BC';

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="sessions") */
    private User $user;

    /** @ORM\Column(name="`api_client_id`", type="string", length=40, options={"fixed": true}) */
    private string $apiClientId;

    /** @ORM\Column(name="`old_api_token`", type="string", length=80, options={"fixed": true}, unique=true, nullable=true) */
    private ?string $oldApiToken = null;

    /** @ORM\Column(name="`current_api_token`", type="string", length=80, options={"fixed": true}, unique=true) */
    private string $currentApiToken;

    /** @ORM\Column(name="`refreshed_at`", type="datetime") */
    private DateTime $refreshedAt;

    /** @ORM\Column(name="`is_locked`", type="boolean") */
    private bool $isLocked = false;

    public function __construct(
        string $id,
        User $user,
        string $apiClientId,
        string $currentApiToken,
        DateTime $refreshedAt,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->apiClientId = $apiClientId;
        $this->currentApiToken = $currentApiToken;
        $this->refreshedAt = $refreshedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setApiClientId(string $apiClientId): self
    {
        $this->apiClientId = $apiClientId;

        return $this;
    }

    public function getApiClientId(): string
    {
        return $this->apiClientId;
    }

    public function setOldApiToken(string $oldApiToken): self
    {
        $this->oldApiToken = $oldApiToken;

        return $this;
    }

    public function getOldApiToken(): ?string
    {
        return $this->oldApiToken;
    }

    public function setCurrentApiToken(string $currentApiToken): self
    {
        $this->currentApiToken = $currentApiToken;

        return $this;
    }

    public function getCurrentApiToken(): string
    {
        return $this->currentApiToken;
    }

    public function setRefreshedAt(DateTime $refreshedAt): self
    {
        $this->refreshedAt = $refreshedAt;

        return $this;
    }

    public function getRefreshedAt(): DateTime
    {
        return (new DateTimeUTC())->createDateTimeInstance($this->refreshedAt->format('Y-m-d H:i:s'));
    }

    public function setIsLocked(bool $isLocked): self
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }
}
