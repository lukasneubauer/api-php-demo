<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\CannotDeleteTeacherWithOngoingCourses;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Iterator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class CannotDeleteTeacherWithOngoingCoursesTest extends TestCase
{
    public function testCheckIfCannotDeleteTeacherWithOngoingCoursesDoesNotThrowExceptionBecauseUserIsNotTeacher(): void
    {
        try {
            $collection = m::mock(Collection::class);
            $user = m::mock(User::class);
            $user->shouldReceive('getTeacherCourses')
                ->times(1)
                ->andReturn($collection);
            $user->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(false);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new CannotDeleteTeacherWithOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteTeacherWithOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteTeacherWithOngoingCoursesDoesNotThrowExceptionBecauseTeacherHasNoCourses(): void
    {
        try {
            $collection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(0)
                ->getMock();
            $user = m::mock(User::class);
            $user->shouldReceive('getTeacherCourses')
                ->times(1)
                ->andReturn($collection);
            $user->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new CannotDeleteTeacherWithOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteTeacherWithOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteTeacherWithOngoingCoursesDoesNotThrowExceptionBecauseCourseIsNotActive(): void
    {
        try {
            $studentsCollection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(0)
                ->getMock();

            $lessonsCollection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(0)
                ->getMock();

            $course = m::mock(Course::class);
            $course->shouldReceive('getStudents')
                ->times(1)
                ->andReturn($studentsCollection);
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection);
            $course->shouldReceive('isActive')
                ->times(1)
                ->andReturn(false);

            $coursesIterator = m::mock(Iterator::class);
            $coursesIterator->shouldReceive('rewind')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(true);
            $coursesIterator->shouldReceive('current')
                ->times(1)
                ->andReturn($course);
            $coursesIterator->shouldReceive('next')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(false);

            $coursesCollection = m::mock(Collection::class);
            $coursesCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $coursesCollection->shouldReceive('getIterator')
                ->times(1)
                ->andReturn($coursesIterator);

            $user = m::mock(User::class);
            $user->shouldReceive('getTeacherCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteTeacherWithOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteTeacherWithOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteTeacherWithOngoingCoursesDoesNotThrowExceptionBecauseCourseHasNoStudents(): void
    {
        try {
            $studentsCollection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(0)
                ->getMock();

            $lessonsCollection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(0)
                ->getMock();

            $course = m::mock(Course::class);
            $course->shouldReceive('getStudents')
                ->times(1)
                ->andReturn($studentsCollection);
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection);
            $course->shouldReceive('isActive')
                ->times(1)
                ->andReturn(true);

            $coursesIterator = m::mock(Iterator::class);
            $coursesIterator->shouldReceive('rewind')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(true);
            $coursesIterator->shouldReceive('current')
                ->times(1)
                ->andReturn($course);
            $coursesIterator->shouldReceive('next')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(false);

            $coursesCollection = m::mock(Collection::class);
            $coursesCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $coursesCollection->shouldReceive('getIterator')
                ->times(1)
                ->andReturn($coursesIterator);

            $user = m::mock(User::class);
            $user->shouldReceive('getTeacherCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteTeacherWithOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteTeacherWithOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteTeacherWithOngoingCoursesDoesNotThrowExceptionBecauseCourseIsInThePast(): void
    {
        try {
            $studentsCollection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(1)
                ->getMock();

            $lessonStart = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() - 60 * 60 * 2)
                ->getMock();
            $lessonEnd = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() - 60 * 60)
                ->getMock();

            $lesson = m::mock(Lesson::class);
            $lesson->shouldReceive('getFrom')
                ->times(1)
                ->andReturn($lessonStart);
            $lesson->shouldReceive('getTo')
                ->times(1)
                ->andReturn($lessonEnd);

            $lessonsCollection = m::mock(Collection::class);
            $lessonsCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $lessonsCollection->shouldReceive('first')
                ->times(1)
                ->andReturn($lesson);
            $lessonsCollection->shouldReceive('last')
                ->times(1)
                ->andReturn($lesson);

            $course = m::mock(Course::class);
            $course->shouldReceive('getStudents')
                ->times(1)
                ->andReturn($studentsCollection);
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection);
            $course->shouldReceive('isActive')
                ->times(1)
                ->andReturn(true);

            $coursesIterator = m::mock(Iterator::class);
            $coursesIterator->shouldReceive('rewind')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(true);
            $coursesIterator->shouldReceive('current')
                ->times(1)
                ->andReturn($course);
            $coursesIterator->shouldReceive('next')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(false);

            $coursesCollection = m::mock(Collection::class);
            $coursesCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $coursesCollection->shouldReceive('getIterator')
                ->times(1)
                ->andReturn($coursesIterator);

            $user = m::mock(User::class);
            $user->shouldReceive('getTeacherCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteTeacherWithOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteTeacherWithOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteTeacherWithOngoingCoursesDoesNotThrowExceptionBecauseCourseIsInTheFuture(): void
    {
        try {
            $studentsCollection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(1)
                ->getMock();

            $lessonStart = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() + 60 * 60)
                ->getMock();
            $lessonEnd = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() + 60 * 60 * 2)
                ->getMock();

            $lesson = m::mock(Lesson::class);
            $lesson->shouldReceive('getFrom')
                ->times(1)
                ->andReturn($lessonStart);
            $lesson->shouldReceive('getTo')
                ->times(1)
                ->andReturn($lessonEnd);

            $lessonsCollection = m::mock(Collection::class);
            $lessonsCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $lessonsCollection->shouldReceive('first')
                ->times(1)
                ->andReturn($lesson);
            $lessonsCollection->shouldReceive('last')
                ->times(1)
                ->andReturn($lesson);

            $course = m::mock(Course::class);
            $course->shouldReceive('getStudents')
                ->times(1)
                ->andReturn($studentsCollection);
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection);
            $course->shouldReceive('isActive')
                ->times(1)
                ->andReturn(true);

            $coursesIterator = m::mock(Iterator::class);
            $coursesIterator->shouldReceive('rewind')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(true);
            $coursesIterator->shouldReceive('current')
                ->times(1)
                ->andReturn($course);
            $coursesIterator->shouldReceive('next')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(false);

            $coursesCollection = m::mock(Collection::class);
            $coursesCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $coursesCollection->shouldReceive('getIterator')
                ->times(1)
                ->andReturn($coursesIterator);

            $user = m::mock(User::class);
            $user->shouldReceive('getTeacherCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteTeacherWithOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteTeacherWithOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteTeacherWithOngoingCoursesThrowsException(): void
    {
        try {
            $studentsCollection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(1)
                ->getMock();

            $lessonStart = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() - 60 * 60)
                ->getMock();
            $lessonEnd = m::mock(DateTime::class)
                ->shouldReceive('getTimestamp')
                ->times(1)
                ->andReturn(\time() + 60 * 60)
                ->getMock();

            $lesson = m::mock(Lesson::class);
            $lesson->shouldReceive('getFrom')
                ->times(1)
                ->andReturn($lessonStart);
            $lesson->shouldReceive('getTo')
                ->times(1)
                ->andReturn($lessonEnd);

            $lessonsCollection = m::mock(Collection::class);
            $lessonsCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $lessonsCollection->shouldReceive('first')
                ->times(1)
                ->andReturn($lesson);
            $lessonsCollection->shouldReceive('last')
                ->times(1)
                ->andReturn($lesson);

            $course = m::mock(Course::class);
            $course->shouldReceive('getStudents')
                ->times(1)
                ->andReturn($studentsCollection);
            $course->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection);
            $course->shouldReceive('isActive')
                ->times(1)
                ->andReturn(true);

            $coursesIterator = m::mock(Iterator::class);
            $coursesIterator->shouldReceive('rewind')
                ->times(1);
            $coursesIterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(true);
            $coursesIterator->shouldReceive('current')
                ->times(1)
                ->andReturn($course);

            $coursesCollection = m::mock(Collection::class);
            $coursesCollection->shouldReceive('count')
                ->times(1)
                ->andReturn(1);
            $coursesCollection->shouldReceive('getIterator')
                ->times(1)
                ->andReturn($coursesIterator);

            $user = m::mock(User::class);
            $user->shouldReceive('getTeacherCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteTeacherWithOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteTeacherWithOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(35, $data['error']['code']);
            $this->assertSame('Cannot delete teacher with ongoing courses.', $data['error']['message']);
            $this->assertSame('Cannot delete teacher with ongoing courses.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
