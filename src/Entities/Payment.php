<?php

declare(strict_types=1);

namespace App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="`payments`")
 * @ORM\Entity
 */
class Payment
{
    use Id;
    use Timestamp;

    /** @ORM\ManyToOne(targetEntity="Course") */
    private Course $course;

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="payments") */
    private User $student;

    /** @ORM\Column(name="`price`", type="integer", options={"unsigned": true}) */
    private int $price;

    /** @ORM\Column(name="`is_refund_requested`", type="boolean") */
    private bool $isRefundRequested = false;

    /** @ORM\Column(name="`is_refunded`", type="boolean") */
    private bool $isRefunded = false;

    /** @ORM\Column(name="`is_refund_closed`", type="boolean") */
    private bool $isRefundClosed = false;

    public function __construct(
        string $id,
        Course $course,
        User $student,
        int $price,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->course = $course;
        $this->student = $student;
        $this->price = $price;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function getStudent(): User
    {
        return $this->student;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setIsRefundRequested(bool $isRefundRequested): self
    {
        $this->isRefundRequested = $isRefundRequested;

        return $this;
    }

    public function isRefundRequested(): bool
    {
        return $this->isRefundRequested;
    }

    public function setIsRefunded(bool $isRefunded): self
    {
        $this->isRefunded = $isRefunded;

        return $this;
    }

    public function isRefunded(): bool
    {
        return $this->isRefunded;
    }

    public function setIsRefundClosed(bool $isRefundClosed): self
    {
        $this->isRefundClosed = $isRefundClosed;

        return $this;
    }

    public function isRefundClosed(): bool
    {
        return $this->isRefundClosed;
    }
}
