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
use App\Validators\CannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class CannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequestTest extends TestCase
{
    public function testCheckIfCannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequestDoesNotThrowException(): void
    {
        try {
            $course = m::mock(Course::class);
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();

            $paymentRepository = m::mock(PaymentRepository::class)
                ->shouldReceive('getByCourseAndStudentClosed')
                ->times(1)
                ->andReturnNull()
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

            $validator = new CannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest($courseRepository, $paymentRepository, $sessionRepository);
            $validator->checkIfCannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest(
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

    public function testCheckIfCannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequestThrowsException(): void
    {
        try {
            $course = m::mock(Course::class);
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();

            $payment = m::mock(Payment::class);
            $paymentRepository = m::mock(PaymentRepository::class)
                ->shouldReceive('getByCourseAndStudentClosed')
                ->times(1)
                ->andReturn($payment)
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

            $validator = new CannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest($courseRepository, $paymentRepository, $sessionRepository);
            $validator->checkIfCannotRequestRefundForThePaymentWhichAlreadyHasClosedRefundRequest(
                new HeaderBag(['Api-Token' => 'zqyn5ffaixt7b6x7r2zovmmpdj3z4aznftduf573']),
                [
                    'courseId' => 'a3c03a4a-c0ba-4412-a21d-3a3a34a11cf0',
                ]
            );

            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(64, $data['error']['code']);
            $this->assertSame('Cannot request refund for the payment which already has closed refund request.', $data['error']['message']);
            $this->assertSame('Cannot request refund for the payment which already has closed refund request.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
