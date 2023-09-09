<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\DeleteLessonPersister;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\DeleteLessonRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteLessonController
{
    private DeleteLessonPersister $deleteLessonPersister;

    private DeleteLessonRequestValidator $deleteLessonRequestValidator;

    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        DeleteLessonPersister $deleteLessonPersister,
        DeleteLessonRequestValidator $deleteLessonRequestValidator,
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->deleteLessonPersister = $deleteLessonPersister;
        $this->deleteLessonRequestValidator = $deleteLessonRequestValidator;
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/delete-lesson", name="delete-lesson")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->deleteLessonRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->deleteLessonPersister->deleteLesson($data);
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
