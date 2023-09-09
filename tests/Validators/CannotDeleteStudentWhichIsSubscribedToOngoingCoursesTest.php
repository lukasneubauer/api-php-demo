<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\CannotDeleteStudentWhichIsSubscribedToOngoingCourses;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Iterator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class CannotDeleteStudentWhichIsSubscribedToOngoingCoursesTest extends TestCase
{
    public function testCheckIfCannotDeleteStudentWhichIsSubscribedToOngoingCoursesDoesNotThrowExceptionBecauseUserIsNotStudent(): void
    {
        try {
            $collection = m::mock(Collection::class);
            $user = m::mock(User::class);
            $user->shouldReceive('getStudentCourses')
                ->times(1)
                ->andReturn($collection);
            $user->shouldReceive('isStudent')
                ->times(1)
                ->andReturn(false);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new CannotDeleteStudentWhichIsSubscribedToOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteStudentWhichIsSubscribedToOngoingCoursesDoesNotThrowExceptionBecauseStudentIsNotSubscribedToAnyCourse(): void
    {
        try {
            $collection = m::mock(Collection::class)
                ->shouldReceive('count')
                ->times(1)
                ->andReturn(0)
                ->getMock();
            $user = m::mock(User::class);
            $user->shouldReceive('getStudentCourses')
                ->times(1)
                ->andReturn($collection);
            $user->shouldReceive('isStudent')
                ->times(1)
                ->andReturn(true);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new CannotDeleteStudentWhichIsSubscribedToOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteStudentWhichIsSubscribedToOngoingCoursesDoesNotThrowExceptionBecauseCourseIsInThePast(): void
    {
        try {
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

            $course = m::mock(Course::class)
                ->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection)
                ->getMock();

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
            $user->shouldReceive('getStudentCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isStudent')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteStudentWhichIsSubscribedToOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteStudentWhichIsSubscribedToOngoingCoursesDoesNotThrowExceptionBecauseCourseIsInTheFuture(): void
    {
        try {
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

            $course = m::mock(Course::class)
                ->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection)
                ->getMock();

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
            $user->shouldReceive('getStudentCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isStudent')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteStudentWhichIsSubscribedToOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotDeleteStudentWhichIsSubscribedToOngoingCoursesThrowsException(): void
    {
        try {
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

            $course = m::mock(Course::class)
                ->shouldReceive('getLessons')
                ->times(1)
                ->andReturn($lessonsCollection)
                ->getMock();

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
            $user->shouldReceive('getStudentCourses')
                ->times(1)
                ->andReturn($coursesCollection);
            $user->shouldReceive('isStudent')
                ->times(1)
                ->andReturn(true);

            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();

            $validator = new CannotDeleteStudentWhichIsSubscribedToOngoingCourses($userRepository);
            $validator->checkIfCannotDeleteStudentWhichIsSubscribedToOngoingCourses(['id' => '64fe2c8e-2043-4fe8-ae78-16c200f8ea9e']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(36, $data['error']['code']);
            $this->assertSame('Cannot delete student which is subscribed to ongoing courses.', $data['error']['message']);
            $this->assertSame('Cannot delete student which is subscribed to ongoing courses.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
