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

class CannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest
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
    public function checkIfCannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest(HeaderBag $headers, array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));
        $payment = $this->paymentRepository->getByCourseAndStudentOpened($course, $session->getUser());

        if ($payment !== null) {
            $error = Error::cannotRequestRefundForThePaymentWhichAlreadyHasOpenedRefundRequest();
            $message = Emsg::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_ALREADY_HAS_OPENED_REFUND_REQUEST;
            throw new ValidationException($error, $message);
        }
    }
}
