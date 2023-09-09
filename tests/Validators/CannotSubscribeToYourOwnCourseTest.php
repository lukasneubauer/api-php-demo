<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Session;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use App\Validators\CannotSubscribeToYourOwnCourse;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class CannotSubscribeToYourOwnCourseTest extends TestCase
{
    public function testCheckIfCannotSubscribeToYourOwnCourseDoesNotThrowException(): void
    {
        try {
            $teacher = m::mock(User::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('3b1e2d43-f67e-4e7d-8544-9cb6da498581')
                ->getMock();
            $course = m::mock(Course::class)
                ->shouldReceive('getTeacher')
                ->times(1)
                ->andReturn($teacher)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();

            $user = m::mock(User::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('5d700dd0-7c0a-4e05-a1e8-2ee190b77c0c')
                ->getMock();
            $session = m::mock(Session::class)
                ->shouldReceive('getUser')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();

            $validator = new CannotSubscribeToYourOwnCourse($courseRepository, $sessionRepository);
            $validator->checkIfCannotSubscribeToYourOwnCourse(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'courseId' => '6fd21fb4-5787-4113-9e48-44ded2492608',
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotSubscribeToYourOwnCourseThrowsException(): void
    {
        try {
            $teacher = m::mock(User::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('3b1e2d43-f67e-4e7d-8544-9cb6da498581')
                ->getMock();
            $course = m::mock(Course::class)
                ->shouldReceive('getTeacher')
                ->times(1)
                ->andReturn($teacher)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();

            $user = m::mock(User::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('3b1e2d43-f67e-4e7d-8544-9cb6da498581')
                ->getMock();
            $session = m::mock(Session::class)
                ->shouldReceive('getUser')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();

            $validator = new CannotSubscribeToYourOwnCourse($courseRepository, $sessionRepository);
            $validator->checkIfCannotSubscribeToYourOwnCourse(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'courseId' => '6fd21fb4-5787-4113-9e48-44ded2492608',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(49, $data['error']['code']);
            $this->assertSame('Cannot subscribe to your own course.', $data['error']['message']);
            $this->assertSame('Cannot subscribe to your own course.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
