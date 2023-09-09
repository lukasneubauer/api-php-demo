<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\Persisters\LogoutPersister;
use App\RequestValidators\LogoutRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController
{
    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private LockSessionPersister $lockSessionPersister;

    private LogoutPersister $logoutPersister;

    private LogoutRequestValidator $logoutRequestValidator;

    public function __construct(
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        LockSessionPersister $lockSessionPersister,
        LogoutPersister $logoutPersister,
        LogoutRequestValidator $logoutRequestValidator
    ) {
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->lockSessionPersister = $lockSessionPersister;
        $this->logoutPersister = $logoutPersister;
        $this->logoutRequestValidator = $logoutRequestValidator;
    }

    /**
     * @Route("/-/logout", name="logout")
     */
    public function index(Request $request): Response
    {
        try {
            $this->logoutRequestValidator->validateRequest($request->headers, $request->getMethod());
            $this->logoutPersister->deleteSession();
            return $this->emptySuccessfulResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
