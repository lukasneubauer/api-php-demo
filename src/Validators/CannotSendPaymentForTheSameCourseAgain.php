<?php

declare(strict_types=1);

namespace App\Validators;

use App\Errors\Error;
use App\Errors\ErrorMessage as Emsg;
use App\Exceptions\ValidationException;
use App\Http\ApiHeaders;
use App\Repositories\CourseRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\SessionRepository;
use Symfony\Component\HttpFoundation\HeaderBag;

class CannotSendPaymentForTheSameCourseAgain
{
    private CourseRepository $courseRepository;

    private PaymentRepository $paymentRepository;

    private SessionRepository $sessionRepository;

    public function __construct(
        CourseRepository $courseRepository,
        PaymentRepository $paymentRepository,
        SessionRepository $sessionRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->paymentRepository = $paymentRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws ValidationException
     */
    public function checkIfCannotSendPaymentForTheSameCourseAgain(HeaderBag $headers, array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        $apiToken = (string) $headers->get(ApiHeaders::API_TOKEN);
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $student = $session->getUser();
        $payment = $this->paymentRepository->getByCourseAndStudent($course, $student);
        if ($payment !== null) {
            $error = Error::cannotSendPaymentForTheSameCourseAgain();
            $message = Emsg::CANNOT_SEND_PAYMENT_FOR_THE_SAME_COURSE_AGAIN;
            throw new ValidationException($error, $message);
        }
    }
}
