<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Session;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use App\Repositories\SessionRepository;
use App\Validators\TryingToGetLessonListForCourseWhichIsNotYours;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

final class TryingToGetLessonListForCourseWhichIsNotYoursTest extends TestCase
{
    public function testCheckIfTryingToGetLessonListForCourseWhichIsNotYoursDoesNotThrowException(): void
    {
        try {
            $teacher = m::mock(User::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('0c71ac2a-2e22-4507-846c-77c0de1428a3')
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
                ->andReturn('0c71ac2a-2e22-4507-846c-77c0de1428a3')
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

            $validator = new TryingToGetLessonListForCourseWhichIsNotYours($courseRepository, $sessionRepository);
            $validator->checkIfTryingToGetLessonListForCourseWhichIsNotYours(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                new ParameterBag(['id' => 'cc3488de-ccdb-40c3-b964-a4e04a51314a'])
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfTryingToGetLessonListForCourseWhichIsNotYoursThrowsException(): void
    {
        try {
            $teacher = m::mock(User::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('3d6c08f9-11ad-42aa-8b58-d0c7a4d6683f')
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
                ->andReturn('0c71ac2a-2e22-4507-846c-77c0de1428a3')
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

            $validator = new TryingToGetLessonListForCourseWhichIsNotYours($courseRepository, $sessionRepository);
            $validator->checkIfTryingToGetLessonListForCourseWhichIsNotYours(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                new ParameterBag(['id' => 'cc3488de-ccdb-40c3-b964-a4e04a51314a'])
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(65, $data['error']['code']);
            $this->assertSame('Trying to get lesson list for course which is not yours.', $data['error']['message']);
            $this->assertSame('Trying to get lesson list for course which is not yours.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
