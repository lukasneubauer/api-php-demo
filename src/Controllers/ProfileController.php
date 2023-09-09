<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\ProfileRequestValidator;
use App\Responses\ErrorResponse;
use App\Responses\ProfileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController
{
    private ErrorResponse $errorResponse;

    private LockSessionPersister $lockSessionPersister;

    private ProfileRequestValidator $profileRequestValidator;

    private ProfileResponse $profileResponse;

    public function __construct(
        ErrorResponse $errorResponse,
        LockSessionPersister $lockSessionPersister,
        ProfileRequestValidator $profileRequestValidator,
        ProfileResponse $profileResponse
    ) {
        $this->errorResponse = $errorResponse;
        $this->lockSessionPersister = $lockSessionPersister;
        $this->profileRequestValidator = $profileRequestValidator;
        $this->profileResponse = $profileResponse;
    }

    /**
     * @Route("/-/profile", name="profile")
     */
    public function index(Request $request): Response
    {
        try {
            $this->profileRequestValidator->validateRequest($request->headers, $request->getMethod());
            return $this->profileResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
