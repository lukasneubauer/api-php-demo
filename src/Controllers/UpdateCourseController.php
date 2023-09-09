<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\LockSessionPersister;
use App\Persisters\UpdateCoursePersister;
use App\RequestValidators\UpdateCourseRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UpdateCourseController
{
    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    private UpdateCoursePersister $updateCoursePersister;

    private UpdateCourseRequestValidator $updateCourseRequestValidator;

    public function __construct(
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister,
        UpdateCoursePersister $updateCoursePersister,
        UpdateCourseRequestValidator $updateCourseRequestValidator
    ) {
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
        $this->updateCoursePersister = $updateCoursePersister;
        $this->updateCourseRequestValidator = $updateCourseRequestValidator;
    }

    /**
     * @Route("/-/update-course/{id}", name="update-course")
     */
    public function index(Request $request, string $id): Response
    {
        try {
            $request->query->set('id', $id);
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->updateCourseRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $request->query,
                $requestBody,
                $data
            );
            $this->updateCoursePersister->updateCourse($data, $id);
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
