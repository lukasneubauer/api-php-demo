<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\DeleteAvatarPersister;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\DeleteAvatarRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteAvatarController
{
    private DeleteAvatarPersister $deleteAvatarPersister;

    private DeleteAvatarRequestValidator $deleteAvatarRequestValidator;

    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        DeleteAvatarPersister $deleteAvatarPersister,
        DeleteAvatarRequestValidator $deleteAvatarRequestValidator,
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->deleteAvatarPersister = $deleteAvatarPersister;
        $this->deleteAvatarRequestValidator = $deleteAvatarRequestValidator;
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/delete-avatar", name="delete-avatar")
     */
    public function index(Request $request): Response
    {
        try {
            $this->deleteAvatarRequestValidator->validateRequest($request->headers, $request->getMethod());
            $this->deleteAvatarPersister->deleteAvatar();
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
