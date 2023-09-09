<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\LockSessionPersister;
use App\Persisters\UpdateLessonPersister;
use App\RequestValidators\UpdateLessonRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UpdateLessonController
{
    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    private UpdateLessonPersister $updateLessonPersister;

    private UpdateLessonRequestValidator $updateLessonRequestValidator;

    public function __construct(
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister,
        UpdateLessonPersister $updateLessonPersister,
        UpdateLessonRequestValidator $updateLessonRequestValidator
    ) {
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
        $this->updateLessonPersister = $updateLessonPersister;
        $this->updateLessonRequestValidator = $updateLessonRequestValidator;
    }

    /**
     * @Route("/-/update-lesson/{id}", name="update-lesson")
     */
    public function index(Request $request, string $id): Response
    {
        try {
            $request->query->set('id', $id);
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->updateLessonRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $request->query,
                $requestBody,
                $data
            );
            $this->updateLessonPersister->updateLesson($data, $id);
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
