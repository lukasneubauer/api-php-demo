<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Session;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\SessionRepository;
use App\Validators\CannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo;
use Doctrine\Common\Collections\Collection;
use Iterator;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class CannotSendPaymentForTheCourseToWhichYouAreNotSubscribedToTest extends TestCase
{
    public function testCheckIfCannotSendPaymentForTheCourseToWhichYouAreNotSubscribedToDoesNotThrowException(): void
    {
        try {
            $course = m::mock(Course::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('a3c03a4a-c0ba-4412-a21d-3a3a34a11cf0')
                ->getMock();
            $iterator = m::mock(Iterator::class);
            $iterator->shouldReceive('rewind')
                ->times(1);
            $iterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(true);
            $iterator->shouldReceive('current')
                ->times(1)
                ->andReturn($course);
            $iterator->shouldReceive('next')
                ->times(1);
            $iterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(false);
            $collection = m::mock(Collection::class)
                ->shouldReceive('getIterator')
                ->times(1)
                ->andReturn($iterator)
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getStudentCourses')
                ->times(1)
                ->andReturn($collection)
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
            $validator = new CannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo($sessionRepository);
            $validator->checkIfCannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'courseId' => 'a3c03a4a-c0ba-4412-a21d-3a3a34a11cf0',
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotSendPaymentForTheCourseToWhichYouAreNotSubscribedToThrowsExceptionBecauseOfInvalidToken(): void
    {
        try {
            $course = m::mock(Course::class)
                ->shouldReceive('getId')
                ->times(1)
                ->andReturn('ef87d0f1-9142-42b1-be41-779c9d918bdc')
                ->getMock();
            $iterator = m::mock(Iterator::class);
            $iterator->shouldReceive('rewind')
                ->times(1);
            $iterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(true);
            $iterator->shouldReceive('current')
                ->times(1)
                ->andReturn($course);
            $iterator->shouldReceive('next')
                ->times(1);
            $iterator->shouldReceive('valid')
                ->times(1)
                ->andReturn(false);
            $collection = m::mock(Collection::class)
                ->shouldReceive('getIterator')
                ->times(1)
                ->andReturn($iterator)
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getStudentCourses')
                ->times(1)
                ->andReturn($collection)
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
            $validator = new CannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo($sessionRepository);
            $validator->checkIfCannotSendPaymentForTheCourseToWhichYouAreNotSubscribedTo(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'courseId' => 'a3c03a4a-c0ba-4412-a21d-3a3a34a11cf0',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(58, $data['error']['code']);
            $this->assertSame('Cannot send payment for the course to which you are not subscribed to.', $data['error']['message']);
            $this->assertSame('Cannot send payment for the course to which you are not subscribed to.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
