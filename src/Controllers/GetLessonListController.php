<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\Repositories\CourseRepository;
use App\RequestValidators\GetLessonListRequestValidator;
use App\Responses\ErrorResponse;
use App\Responses\GetLessonListResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetLessonListController
{
    private CourseRepository $courseRepository;

    private ErrorResponse $errorResponse;

    private GetLessonListRequestValidator $getLessonListRequestValidator;

    private GetLessonListResponse $getLessonListResponse;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        CourseRepository $courseRepository,
        ErrorResponse $errorResponse,
        GetLessonListRequestValidator $getLessonListRequestValidator,
        GetLessonListResponse $getLessonListResponse,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->courseRepository = $courseRepository;
        $this->errorResponse = $errorResponse;
        $this->getLessonListRequestValidator = $getLessonListRequestValidator;
        $this->getLessonListResponse = $getLessonListResponse;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/get-lesson-list/{id}", name="get-lesson-list")
     */
    public function index(Request $request, string $id): Response
    {
        try {
            $request->query->set('id', $id);
            $this->getLessonListRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $request->query
            );
            $course = $this->courseRepository->getById($id);
            return $this->getLessonListResponse->createResponse($course);
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
