<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\DateTime\DateTimeUTC;
use App\Entities\Course;
use App\EntityFactories\LessonFactory;
use App\Generators\UuidGenerator;
use DateTime;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LessonFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = m::mock(UuidGenerator::class)
            ->shouldReceive('generateUuid')
            ->times(1)
            ->andReturn('24887dd6-df68-4938-a4a1-49c6401a0389')
            ->getMock();

        $lessonFactory = new LessonFactory($uuidGenerator);

        /** @var Course $course */
        $course = m::mock(Course::class);

        $now = (new DateTimeUTC())->createDateTimeInstance();

        $lesson = $lessonFactory->create(
            $course,
            (new DateTimeUTC())->createDateTimeInstance('2000-01-01 16:00:00'),
            (new DateTimeUTC())->createDateTimeInstance('2000-01-01 18:00:00'),
            'Lorem ipsum',
            $now
        );

        $this->assertSame('24887dd6-df68-4938-a4a1-49c6401a0389', $lesson->getId());
        $this->assertInstanceOf(Course::class, $lesson->getCourse());
        $this->assertInstanceOf(DateTime::class, $lesson->getFrom());
        $this->assertSame('2000-01-01 16:00:00', $lesson->getFrom()->format('Y-m-d H:i:s'));
        $this->assertInstanceOf(DateTime::class, $lesson->getTo());
        $this->assertSame('2000-01-01 18:00:00', $lesson->getTo()->format('Y-m-d H:i:s'));
        $this->assertSame('Lorem ipsum', $lesson->getName());
        $this->assertInstanceOf(DateTime::class, $lesson->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $lesson->getUpdatedAt());
        $this->assertSame($lesson->getCreatedAt()->getTimestamp(), $lesson->getUpdatedAt()->getTimestamp());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
