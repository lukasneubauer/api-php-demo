<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\DateTime\DateTimeUTC;
use App\Entities\Subject;
use App\Entities\User;
use App\EntityFactories\CourseFactory;
use App\Generators\UuidGenerator;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CourseFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = m::mock(UuidGenerator::class)
            ->shouldReceive('generateUuid')
            ->times(1)
            ->andReturn('24887dd6-df68-4938-a4a1-49c6401a0389')
            ->getMock();

        $courseFactory = new CourseFactory($uuidGenerator);

        /** @var Subject $subject */
        $subject = m::mock(Subject::class);

        /** @var User $user */
        $user = m::mock(User::class);

        $now = (new DateTimeUTC())->createDateTimeInstance();

        $course = $courseFactory->create(
            $subject,
            $user,
            'Lorem ipsum',
            25000,
            $now
        );

        $this->assertSame('24887dd6-df68-4938-a4a1-49c6401a0389', $course->getId());
        $this->assertInstanceOf(Subject::class, $course->getSubject());
        $this->assertInstanceOf(User::class, $course->getTeacher());
        $this->assertSame('Lorem ipsum', $course->getName());
        $this->assertSame(25000, $course->getPrice());
        $this->assertFalse($course->isActive());
        $this->assertInstanceOf(DateTime::class, $course->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $course->getUpdatedAt());
        $this->assertSame($course->getCreatedAt()->getTimestamp(), $course->getUpdatedAt()->getTimestamp());
        $this->assertInstanceOf(Collection::class, $course->getStudents());
        $this->assertCount(0, $course->getStudents());
        $this->assertInstanceOf(Collection::class, $course->getLessons());
        $this->assertCount(0, $course->getLessons());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
