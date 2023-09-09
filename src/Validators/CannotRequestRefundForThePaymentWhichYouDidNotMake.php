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

class CannotRequestRefundForThePaymentWhichYouDidNotMake
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
    public function checkIfCannotRequestRefundForThePaymentWhichYouDidNotMake(HeaderBag $headers, array $data): void
    {
        $course = $this->courseRepository->getById($data['courseId']);
        $session = $this->sessionRepository->getByApiToken((string) $headers->get(ApiHeaders::API_TOKEN));
        $payments = $this->paymentRepository->getAllByCourseAndStudent($course, $session->getUser());

        if (\count($payments) === 0) {
            $error = Error::cannotRequestRefundForThePaymentWhichYouDidNotMake();
            $message = Emsg::CANNOT_REQUEST_REFUND_FOR_THE_PAYMENT_WHICH_YOU_DID_NOT_MAKE;
            throw new ValidationException($error, $message);
        }
    }
}
