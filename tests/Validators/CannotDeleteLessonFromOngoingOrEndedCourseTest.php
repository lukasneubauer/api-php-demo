<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Exceptions\ValidationException;
use App\Repositories\LessonRepository;
use App\Validators\CannotDeleteLessonFromOngoingOrEndedCourse;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class CannotDeleteLessonFromOngoingOrEndedCourseTest extends TestCase
{
    /**
     * @dataProvider getArgs
     */
    public function testCheckIfCannotDeleteLessonFromOngoingOrEndedCourseDoesNotThrowException(
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
            $collection = m::mock(Collection::class)
                ->shouldReceive('offsetGet')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($collection)
                ->getMock();
            $firstCourseLesson = m::mock(Lesson::class)
                ->shouldReceive('getCourse')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($firstCourseLesson)
                ->getMock();

            $validator = new CannotDeleteLessonFromOngoingOrEndedCourse($lessonRepository);
            $validator->checkIfCannotDeleteLessonFromOngoingOrEndedCourse(['id' => '7ec270ef-4213-4a3b-9378-1c0da667e9f3']);
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

    public function testCheckIfCannotDeleteLessonFromOngoingOrEndedCourseThrowsException(): void
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
            $collection = m::mock(Collection::class)
                ->shouldReceive('offsetGet')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($collection)
                ->getMock();
            $firstCourseLesson = m::mock(Lesson::class)
                ->shouldReceive('getCourse')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($firstCourseLesson)
                ->getMock();

            $validator = new CannotDeleteLessonFromOngoingOrEndedCourse($lessonRepository);
            $validator->checkIfCannotDeleteLessonFromOngoingOrEndedCourse(['id' => '7ec270ef-4213-4a3b-9378-1c0da667e9f3']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(32, $data['error']['code']);
            $this->assertSame('Cannot delete lesson from ongoing or ended course.', $data['error']['message']);
            $this->assertSame('Cannot delete lesson from ongoing or ended course.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
