<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use App\Validators\CannotSendPaymentForTheOngoingOrEndedCourse;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class CannotSendPaymentForTheOngoingOrEndedCourseTest extends TestCase
{
    public function testCheckIfCannotSendPaymentForTheOngoingOrEndedCourseDoesNotThrowException(): void
    {
        try {
            $dateTime = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() + 10)
                ->getMock();
            $lesson = m::mock(Lesson::class)
                ->shouldReceive('getFrom')
                ->times(1)
                ->andReturn($dateTime)
                ->getMock();
            $collection = m::mock(Collection::class)
                ->shouldReceive('offsetGet')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();
            $course = m::mock(Course::class)
                ->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($collection)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $validator = new CannotSendPaymentForTheOngoingOrEndedCourse($courseRepository);
            $validator->checkIfCannotSendPaymentForTheOngoingOrEndedCourse(['courseId' => '6fd21fb4-5787-4113-9e48-44ded2492608']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotSendPaymentForTheOngoingOrEndedCourseThrowsException(): void
    {
        try {
            $dateTime = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() - 10)
                ->getMock();
            $lesson = m::mock(Lesson::class)
                ->shouldReceive('getFrom')
                ->times(1)
                ->andReturn($dateTime)
                ->getMock();
            $collection = m::mock(Collection::class)
                ->shouldReceive('offsetGet')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();
            $course = m::mock(Course::class)
                ->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($collection)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $validator = new CannotSendPaymentForTheOngoingOrEndedCourse($courseRepository);
            $validator->checkIfCannotSendPaymentForTheOngoingOrEndedCourse(['courseId' => '6fd21fb4-5787-4113-9e48-44ded2492608']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(60, $data['error']['code']);
            $this->assertSame('Cannot send payment for the ongoing or ended course.', $data['error']['message']);
            $this->assertSame('Cannot send payment for the ongoing or ended course.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
