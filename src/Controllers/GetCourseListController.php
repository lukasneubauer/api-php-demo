<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\GetCourseListRequestValidator;
use App\Responses\ErrorResponse;
use App\Responses\GetCourseListResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetCourseListController
{
    private ErrorResponse $errorResponse;

    private GetCourseListRequestValidator $getCourseListRequestValidator;

    private GetCourseListResponse $getCourseListResponse;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        ErrorResponse $errorResponse,
        GetCourseListRequestValidator $getCourseListRequestValidator,
        GetCourseListResponse $getCourseListResponse,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->errorResponse = $errorResponse;
        $this->getCourseListRequestValidator = $getCourseListRequestValidator;
        $this->getCourseListResponse = $getCourseListResponse;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/get-course-list", name="get-course-list")
     */
    public function index(Request $request): Response
    {
        try {
            $this->getCourseListRequestValidator->validateRequest($request->headers, $request->getMethod());
            return $this->getCourseListResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
