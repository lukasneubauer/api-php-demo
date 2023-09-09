<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\DateTime\DateTimeUTC;
use App\Entities\User;
use App\EntityFactories\SubjectFactory;
use App\Generators\UuidGenerator;
use DateTime;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SubjectFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = m::mock(UuidGenerator::class)
            ->shouldReceive('generateUuid')
            ->times(1)
            ->andReturn('24887dd6-df68-4938-a4a1-49c6401a0389')
            ->getMock();

        $subjectFactory = new SubjectFactory($uuidGenerator);

        /** @var User $teacher */
        $teacher = m::mock(User::class)
            ->shouldReceive('getId')
            ->times(1)
            ->andReturn('7ac515ea-4963-4a44-a293-5098b92266f1')
            ->getMock();

        $now = (new DateTimeUTC())->createDateTimeInstance();

        $subject = $subjectFactory->create($teacher, 'Lorem ipsum', $now);

        $this->assertSame('24887dd6-df68-4938-a4a1-49c6401a0389', $subject->getId());
        $this->assertInstanceOf(User::class, $subject->getCreatedBy());
        $this->assertSame('7ac515ea-4963-4a44-a293-5098b92266f1', $subject->getCreatedBy()->getId());
        $this->assertSame('Lorem ipsum', $subject->getName());
        $this->assertInstanceOf(DateTime::class, $subject->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $subject->getUpdatedAt());
        $this->assertSame($subject->getCreatedAt()->getTimestamp(), $subject->getUpdatedAt()->getTimestamp());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
