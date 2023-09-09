<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\Repositories\CourseRepository;
use App\RequestValidators\GetCourseRequestValidator;
use App\Responses\ErrorResponse;
use App\Responses\GetCourseResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetCourseController
{
    private CourseRepository $courseRepository;

    private ErrorResponse $errorResponse;

    private GetCourseRequestValidator $getCourseRequestValidator;

    private GetCourseResponse $getCourseResponse;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        CourseRepository $courseRepository,
        ErrorResponse $errorResponse,
        GetCourseRequestValidator $getCourseRequestValidator,
        GetCourseResponse $getCourseResponse,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->courseRepository = $courseRepository;
        $this->errorResponse = $errorResponse;
        $this->getCourseRequestValidator = $getCourseRequestValidator;
        $this->getCourseResponse = $getCourseResponse;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/get-course/{id}", name="get-course")
     */
    public function index(Request $request, string $id): Response
    {
        try {
            $request->query->set('id', $id);
            $this->getCourseRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $request->query
            );
            $course = $this->courseRepository->getById($id);
            return $this->getCourseResponse->createResponse($course);
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
