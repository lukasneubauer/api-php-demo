<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\CreateLessonPersister;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\CreateLessonRequestValidator;
use App\Responses\CreateLessonResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateLessonController
{
    private CreateLessonPersister $createLessonPersister;

    private CreateLessonRequestValidator $createLessonRequestValidator;

    private CreateLessonResponse $createLessonResponse;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        CreateLessonPersister $createLessonPersister,
        CreateLessonRequestValidator $createLessonRequestValidator,
        CreateLessonResponse $createLessonResponse,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->createLessonPersister = $createLessonPersister;
        $this->createLessonRequestValidator = $createLessonRequestValidator;
        $this->createLessonResponse = $createLessonResponse;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/create-lesson", name="create-lesson")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->createLessonRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $newLesson = $this->createLessonPersister->createLesson($data);
            return $this->createLessonResponse->createResponse($newLesson);
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
