<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\VerifyAccessRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerifyAccessController
{
    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private LockSessionPersister $lockSessionPersister;

    private VerifyAccessRequestValidator $verifyAccessRequestValidator;

    public function __construct(
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        LockSessionPersister $lockSessionPersister,
        VerifyAccessRequestValidator $verifyAccessRequestValidator
    ) {
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->lockSessionPersister = $lockSessionPersister;
        $this->verifyAccessRequestValidator = $verifyAccessRequestValidator;
    }

    /**
     * @Route("/-/verify-access", name="verify-access")
     */
    public function index(Request $request): Response
    {
        try {
            $this->verifyAccessRequestValidator->validateRequest($request->headers, $request->getMethod());
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
