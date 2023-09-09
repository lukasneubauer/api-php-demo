<?php

declare(strict_types=1);

namespace App\Entities;

use App\DateTime\DateTimeUTC;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
    /** @ORM\Column(name="`created_at`", type="datetime") */
    private DateTime $createdAt;

    /** @ORM\Column(name="`updated_at`", type="datetime") */
    private DateTime $updatedAt;

    public function getCreatedAt(): DateTime
    {
        return (new DateTimeUTC())->createDateTimeInstance($this->createdAt->format('Y-m-d H:i:s'));
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return (new DateTimeUTC())->createDateTimeInstance($this->updatedAt->format('Y-m-d H:i:s'));
    }
}
