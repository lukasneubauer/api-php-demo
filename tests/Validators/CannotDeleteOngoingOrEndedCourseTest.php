<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use App\Validators\CannotDeleteOngoingOrEndedCourse;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class CannotDeleteOngoingOrEndedCourseTest extends TestCase
{
    public function testCheckIfCannotDeleteOngoingOrEndedCourseDoesNotThrowExceptionBecauseTheCourseHasNoLessons(): void
    {
        try {
            $collection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(0)
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
            $validator = new CannotDeleteOngoingOrEndedCourse($courseRepository);
            $validator->checkIfCannotDeleteOngoingOrEndedCourse(['id' => '7ec270ef-4213-4a3b-9378-1c0da667e9f3']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getArgs
     */
    public function testCheckIfCannotDeleteOngoingOrEndedCourseDoesNotThrowException(
        bool $isActive,
        int $studentCount,
        int $addToCourseStart
    ): void {
        try {
            $course = m::mock(Course::class);

            $course->shouldReceive('isActive')
                ->times(1)
                ->andReturn($isActive)
                ->getMock();

            $students = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn($studentCount)
                ->getMock();
            $course->shouldReceive('getStudents')
                ->times(1)
                ->andReturn($students)
                ->getMock();

            $dateTime = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() + $addToCourseStart)
                ->getMock();
            $lesson = m::mock(Lesson::class)
                ->shouldReceive('getFrom')
                ->times(1)
                ->andReturn($dateTime)
                ->getMock();
            $collection = m::mock(Collection::class);
            $collection->shouldReceive('count')
                ->times(1)
                ->andReturn(5)
                ->getMock();
            $collection->shouldReceive('offsetGet')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($collection)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();

            $validator = new CannotDeleteOngoingOrEndedCourse($courseRepository);
            $validator->checkIfCannotDeleteOngoingOrEndedCourse(['id' => '7ec270ef-4213-4a3b-9378-1c0da667e9f3']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public static function getArgs(): array
    {
        return [
            [
                false,
                10,
                -10,
            ],
            [
                true,
                0,
                -10,
            ],
            [
                true,
                10,
                10,
            ],
        ];
    }

    public function testCheckIfCannotDeleteOngoingOrEndedCourseThrowsException(): void
    {
        try {
            $course = m::mock(Course::class);

            $course->shouldReceive('isActive')
                ->times(1)
                ->andReturn(true)
                ->getMock();

            $students = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(10)
                ->getMock();
            $course->shouldReceive('getStudents')
                ->times(1)
                ->andReturn($students)
                ->getMock();

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
            $collection = m::mock(Collection::class);
            $collection->shouldReceive('count')
                ->times(1)
                ->andReturn(5)
                ->getMock();
            $collection->shouldReceive('offsetGet')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($collection)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();

            $validator = new CannotDeleteOngoingOrEndedCourse($courseRepository);
            $validator->checkIfCannotDeleteOngoingOrEndedCourse(['id' => '7ec270ef-4213-4a3b-9378-1c0da667e9f3']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(33, $data['error']['code']);
            $this->assertSame('Cannot delete ongoing or ended course.', $data['error']['message']);
            $this->assertSame('Cannot delete ongoing or ended course.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
