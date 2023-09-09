<?php

declare(strict_types=1);

namespace App\Entities;

use App\DateTime\DateTimeUTC;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="`users`")
 * @ORM\Entity
 */
class User
{
    use Id;
    use Timestamp;

    /** @var int */
    public const MAX_AUTHENTICATION_FAILURE_ATTEMPTS = 3;

    /** @var string */
    public const UNIQUE_KEY_EMAIL = 'UNIQ_1483A5E96F279BB4';

    /** @var string */
    public const UNIQUE_KEY_TOKEN = 'UNIQ_1483A5E989FC6268';

    /** @ORM\OneToMany(targetEntity="Course", mappedBy="teacher") */
    private Collection $teacherCourses;

    /** @ORM\ManyToMany(targetEntity="Course", mappedBy="students") */
    private Collection $studentCourses;

    /** @ORM\OneToMany(targetEntity="Session", mappedBy="user") */
    private Collection $sessions;

    /** @ORM\OneToMany(targetEntity="Payment", mappedBy="student") */
    private Collection $payments;

    /** @ORM\Column(name="`first_name`", type="string", nullable=true) */
    private ?string $firstName;

    /** @ORM\Column(name="`last_name`", type="string", nullable=true) */
    private ?string $lastName;

    /** @ORM\Column(name="`avatar`", type="text", nullable=true) */
    private ?string $avatar;

    /** @ORM\Column(name="`email`", type="string", unique=true, nullable=true) */
    private ?string $email;

    /** @ORM\Embedded(class="Password", columnPrefix=false) */
    private Password $password;

    /** @ORM\Column(name="`is_teacher`", type="boolean") */
    private bool $isTeacher = false;

    /** @ORM\Column(name="`is_student`", type="boolean") */
    private bool $isStudent = false;

    /** @ORM\Column(name="`timezone`", type="string", nullable=true) */
    private ?string $timezone;

    /** @ORM\Embedded(class="Token", columnPrefix=false) */
    private Token $token;

    /** @ORM\Embedded(class="SecurityCode", columnPrefix=false) */
    private SecurityCode $securityCode;

    /** @ORM\Column(name="`authentication_failures`", type="smallint", options={"unsigned": true}) */
    private int $authenticationFailures = 0;

    /** @ORM\Column(name="`is_locked`", type="boolean") */
    private bool $isLocked = false;

    /** @ORM\Column(name="`is_active`", type="boolean") */
    private bool $isActive;

    public function __construct(
        string $id,
        string $firstName,
        string $lastName,
        ?string $avatar,
        string $email,
        Password $password,
        string $timezone,
        Token $token,
        bool $isActive,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->avatar = $avatar;
        $this->email = $email;
        $this->password = $password;
        $this->timezone = $timezone;
        $this->token = $token;
        $this->securityCode = (new SecurityCode('', (new DateTimeUTC())->createDateTimeInstance()))->setEmpty();
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->teacherCourses = new ArrayCollection();
        $this->studentCourses = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getTeacherCourses(): Collection
    {
        return $this->teacherCourses;
    }

    public function getStudentCourses(): Collection
    {
        return $this->studentCourses;
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function unsetAvatar(): self
    {
        $this->avatar = null;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setPassword(Password $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): ?Password
    {
        return $this->password->isEmpty() === false ? $this->password : null;
    }

    public function setIsTeacher(bool $isTeacher): self
    {
        $this->isTeacher = $isTeacher;

        return $this;
    }

    public function isTeacher(): bool
    {
        return $this->isTeacher;
    }

    public function setIsStudent(bool $isStudent): self
    {
        $this->isStudent = $isStudent;

        return $this;
    }

    public function isStudent(): bool
    {
        return $this->isStudent;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setToken(Token $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): ?Token
    {
        return $this->token->isEmpty() === false ? $this->token : null;
    }

    public function unsetToken(): self
    {
        $this->token->setEmpty();

        return $this;
    }

    public function setSecurityCode(SecurityCode $securityCode): self
    {
        $this->securityCode = $securityCode;

        return $this;
    }

    public function getSecurityCode(): ?SecurityCode
    {
        return $this->securityCode->isEmpty() === false ? $this->securityCode : null;
    }

    public function unsetSecurityCode(): self
    {
        $this->securityCode->setEmpty();

        return $this;
    }

    public function setAuthenticationFailures(int $authenticationFailures): self
    {
        $this->authenticationFailures = $authenticationFailures;

        return $this;
    }

    public function getAuthenticationFailures(): int
    {
        return $this->authenticationFailures;
    }

    public function getAuthenticationFailuresLeft(): int
    {
        return self::MAX_AUTHENTICATION_FAILURE_ATTEMPTS - $this->authenticationFailures;
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

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setEmpty(): self
    {
        $this->firstName = null;
        $this->lastName = null;
        $this->avatar = null;
        $this->email = null;
        $this->password->setEmpty();
        $this->isTeacher = false;
        $this->isStudent = false;
        $this->timezone = null;
        $this->token->setEmpty();
        $this->securityCode->setEmpty();
        $this->authenticationFailures = 0;
        $this->isLocked = false;
        $this->isActive = false;
        $this->updatedAt = (new DateTimeUTC())->createDateTimeInstance();

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->firstName === null
            && $this->lastName === null
            && $this->avatar === null
            && $this->email === null
            && $this->password->isEmpty()
            && $this->isTeacher === false
            && $this->isStudent === false
            && $this->timezone === null
            && $this->token->isEmpty()
            && $this->securityCode->isEmpty()
            && $this->authenticationFailures === 0
            && $this->isLocked === false
            && $this->isActive === false;
    }
}
