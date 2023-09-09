<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Entities\Payment;
use App\Entities\Session;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SessionRepository;
use App\Validators\CannotSendPaymentForTheSameCourseAgain;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class CannotSendPaymentForTheSameCourseAgainTest extends TestCase
{
    public function testCannotSendPaymentForTheSameCourseAgainDoesNotThrowException(): void
    {
        try {
            $course = m::mock(Course::class);
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $user = m::mock(User::class);
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
            $paymentRepository = m::mock(PaymentRepository::class)
                ->shouldReceive('getByCourseAndStudent')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new CannotSendPaymentForTheSameCourseAgain($courseRepository, $paymentRepository, $sessionRepository);
            $validator->checkIfCannotSendPaymentForTheSameCourseAgain(
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

    public function testCannotSendPaymentForTheSameCourseAgainThrowsException(): void
    {
        try {
            $course = m::mock(Course::class);
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $user = m::mock(User::class);
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
            $payment = m::mock(Payment::class);
            $paymentRepository = m::mock(PaymentRepository::class)
                ->shouldReceive('getByCourseAndStudent')
                ->times(1)
                ->andReturn($payment)
                ->getMock();
            $validator = new CannotSendPaymentForTheSameCourseAgain($courseRepository, $paymentRepository, $sessionRepository);
            $validator->checkIfCannotSendPaymentForTheSameCourseAgain(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'courseId' => 'a3c03a4a-c0ba-4412-a21d-3a3a34a11cf0',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(59, $data['error']['code']);
            $this->assertSame('Cannot send payment for the same course again.', $data['error']['message']);
            $this->assertSame('Cannot send payment for the same course again.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
