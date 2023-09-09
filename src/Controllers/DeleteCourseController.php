<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\DeleteCoursePersister;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\DeleteCourseRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteCourseController
{
    private DeleteCoursePersister $deleteCoursePersister;

    private DeleteCourseRequestValidator $deleteCourseRequestValidator;

    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        DeleteCoursePersister $deleteCoursePersister,
        DeleteCourseRequestValidator $deleteCourseRequestValidator,
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->deleteCoursePersister = $deleteCoursePersister;
        $this->deleteCourseRequestValidator = $deleteCourseRequestValidator;
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/delete-course", name="delete-course")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->deleteCourseRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->deleteCoursePersister->deleteCourse($data);
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
