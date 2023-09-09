<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Lesson;
use App\Entities\Session;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\LessonRepository;
use App\Repositories\SessionRepository;
use App\Validators\ToUpdateTheLessonTheUserHasToBeTeacher;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

final class ToUpdateTheLessonTheUserHasToBeTeacherTest extends TestCase
{
    public function testCheckIfTheUserIsTeacherToUpdateTheLessonDoesNotThrowException(): void
    {
        try {
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
            $lesson = m::mock(Lesson::class)
                ->shouldReceive('getCourse')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();

            $validator = new ToUpdateTheLessonTheUserHasToBeTeacher($sessionRepository, $lessonRepository);
            $validator->checkIfTheUserIsTeacherToUpdateTheLesson(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                new ParameterBag(['id' => '6058a731-4fe3-4c77-94ff-8df1536985e0'])
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfTheUserIsTeacherToUpdateTheLessonThrowsException(): void
    {
        try {
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
            $lesson = m::mock(Lesson::class)
                ->shouldReceive('getCourse')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($lesson)
                ->getMock();

            $validator = new ToUpdateTheLessonTheUserHasToBeTeacher($sessionRepository, $lessonRepository);
            $validator->checkIfTheUserIsTeacherToUpdateTheLesson(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                new ParameterBag(['id' => '6058a731-4fe3-4c77-94ff-8df1536985e0'])
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(51, $data['error']['code']);
            $this->assertSame("To update the lesson the user has to be teacher in the given lesson's course.", $data['error']['message']);
            $this->assertSame("To update the lesson the user has to be teacher in the given lesson's course.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
