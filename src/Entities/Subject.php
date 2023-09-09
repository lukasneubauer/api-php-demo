<?php

declare(strict_types=1);

namespace App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="`subjects`")
 * @ORM\Entity
 */
class Subject
{
    use Id;
    use Timestamp;

    /** @var string */
    public const UNIQUE_KEY_NAME = 'UNIQ_AB259917999517A';

    /** @ORM\ManyToOne(targetEntity="User") */
    private ?User $createdBy;

    /** @ORM\Column(name="`name`", type="string", unique=true) */
    private string $name;

    public function __construct(
        string $id,
        ?User $createdBy,
        string $name,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->createdBy = $createdBy;
        $this->name = $name;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
