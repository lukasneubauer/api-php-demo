<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Course;
use App\Entities\Payment;
use App\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PaymentRepository
{
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityRepository = $em->getRepository(Payment::class);
    }

    public function getAllByCourseAndStudent(Course $course, User $student): array
    {
        return $this->entityRepository->findBy(
            [
                'course' => $course,
                'student' => $student,
            ]
        );
    }

    public function getByCourseAndStudent(Course $course, User $student): ?Payment
    {
        return $this->entityRepository->findOneBy(
            [
                'course' => $course,
                'student' => $student,
                'isRefundRequested' => false,
                'isRefundClosed' => false,
            ]
        );
    }

    public function getByCourseAndStudentOpened(Course $course, User $student): ?Payment
    {
        return $this->entityRepository->findOneBy(
            [
                'course' => $course,
                'student' => $student,
                'isRefundRequested' => true,
                'isRefundClosed' => false,
            ]
        );
    }

    public function getByCourseAndStudentClosed(Course $course, User $student): ?Payment
    {
        return $this->entityRepository->findOneBy(
            [
                'course' => $course,
                'student' => $student,
                'isRefundRequested' => true,
                'isRefundClosed' => true,
            ]
        );
    }
}
