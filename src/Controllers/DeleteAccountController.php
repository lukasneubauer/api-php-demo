<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\DeleteAccountPersister;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\DeleteAccountRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteAccountController
{
    private DeleteAccountPersister $deleteAccountPersister;

    private DeleteAccountRequestValidator $deleteAccountRequestValidator;

    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        DeleteAccountPersister $deleteAccountPersister,
        DeleteAccountRequestValidator $deleteAccountRequestValidator,
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->deleteAccountPersister = $deleteAccountPersister;
        $this->deleteAccountRequestValidator = $deleteAccountRequestValidator;
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/delete-account", name="delete-account")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->deleteAccountRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->deleteAccountPersister->deleteAccount($data);
            return $this->emptySuccessfulResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
