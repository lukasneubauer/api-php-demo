<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\DeleteCourseSubscriptionPersister;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\DeleteCourseSubscriptionRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteCourseSubscriptionController
{
    private DeleteCourseSubscriptionPersister $deleteCourseSubscriptionPersister;

    private DeleteCourseSubscriptionRequestValidator $deleteCourseSubscriptionRequestValidator;

    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        DeleteCourseSubscriptionPersister $deleteCourseSubscriptionPersister,
        DeleteCourseSubscriptionRequestValidator $deleteCourseSubscriptionRequestValidator,
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->deleteCourseSubscriptionPersister = $deleteCourseSubscriptionPersister;
        $this->deleteCourseSubscriptionRequestValidator = $deleteCourseSubscriptionRequestValidator;
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/delete-course-subscription", name="delete-course-subscription")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->deleteCourseSubscriptionRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->deleteCourseSubscriptionPersister->deleteCourseSubscription($data);
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
