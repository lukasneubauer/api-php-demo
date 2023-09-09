<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\Repositories\LessonRepository;
use App\RequestValidators\GetLessonRequestValidator;
use App\Responses\ErrorResponse;
use App\Responses\GetLessonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetLessonController
{
    private ErrorResponse $errorResponse;

    private GetLessonRequestValidator $getLessonRequestValidator;

    private GetLessonResponse $getLessonResponse;

    private LessonRepository $lessonRepository;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        ErrorResponse $errorResponse,
        GetLessonRequestValidator $getLessonRequestValidator,
        GetLessonResponse $getLessonResponse,
        LessonRepository $lessonRepository,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->errorResponse = $errorResponse;
        $this->getLessonRequestValidator = $getLessonRequestValidator;
        $this->getLessonResponse = $getLessonResponse;
        $this->lessonRepository = $lessonRepository;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/get-lesson/{id}", name="get-lesson")
     */
    public function index(Request $request, string $id): Response
    {
        try {
            $request->query->set('id', $id);
            $this->getLessonRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $request->query
            );
            $lesson = $this->lessonRepository->getById($id);
            return $this->getLessonResponse->createResponse($lesson);
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
