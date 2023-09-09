<?php

declare(strict_types=1);

namespace App\Entities;

use App\DateTime\DateTimeUTC;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="`lessons`")
 * @ORM\Entity
 */
class Lesson
{
    use Id;
    use Timestamp;

    /** @ORM\ManyToOne(targetEntity="Course", inversedBy="lessons") */
    private Course $course;

    /** @ORM\Column(name="`from`", type="datetime") */
    private DateTime $from;

    /** @ORM\Column(name="`to`", type="datetime") */
    private DateTime $to;

    /** @ORM\Column(name="`name`", type="string") */
    private string $name;

    public function __construct(
        string $id,
        Course $course,
        DateTime $from,
        DateTime $to,
        string $name,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->course = $course;
        $this->from = $from;
        $this->to = $to;
        $this->name = $name;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function setCourse(Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setFrom(DateTime $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getFrom(): DateTime
    {
        return (new DateTimeUTC())->createDateTimeInstance($this->from->format('Y-m-d H:i:s'));
    }

    public function setTo(DateTime $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function getTo(): DateTime
    {
        return (new DateTimeUTC())->createDateTimeInstance($this->to->format('Y-m-d H:i:s'));
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
