<?php

declare(strict_types=1);

namespace App\Entities;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="`courses`")
 * @ORM\Entity
 */
class Course
{
    use Id;
    use Timestamp;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="studentCourses")
     * @ORM\JoinTable(
     *     name="`subscriptions`",
     *     joinColumns={
     *         @ORM\JoinColumn(name="`course_id`", referencedColumnName="`id`")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="`student_id`", referencedColumnName="`id`")
     *     }
     * )
     */
    private Collection $students;

    /**
     * @ORM\OneToMany(targetEntity="Lesson", mappedBy="course")
     * @ORM\OrderBy({"from"="ASC"})
     */
    private Collection $lessons;

    /** @ORM\ManyToOne(targetEntity="Subject") */
    private Subject $subject;

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="teacherCourses") */
    private User $teacher;

    /** @ORM\Column(name="`name`", type="string", nullable=true) */
    private ?string $name;

    /** @ORM\Column(name="`price`", type="integer", options={"unsigned": true}) */
    private int $price;

    /** @ORM\Column(name="`is_active`", type="boolean") */
    private bool $isActive;

    public function __construct(
        string $id,
        Subject $subject,
        User $teacher,
        ?string $name,
        int $price,
        bool $isActive,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->subject = $subject;
        $this->teacher = $teacher;
        $this->name = $name;
        $this->price = $price;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->students = new ArrayCollection();
        $this->lessons = new ArrayCollection();
    }

    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(User $student): void
    {
        if ($this->students->contains($student) === false) {
            $this->students->add($student);
        }
    }

    public function removeStudent(User $student): void
    {
        if ($this->students->contains($student)) {
            $this->students->removeElement($student);
        }
    }

    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function setSubject(Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function setTeacher(User $teacher): self
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getTeacher(): User
    {
        return $this->teacher;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
