<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Emails\AccountActivationEmail;
use App\Exceptions\CouldNotPersistException;
use App\Exceptions\RequestValidationException;
use App\Exceptions\TokenExpiredException;
use App\Json\JsonDecoder;
use App\Links\AccountActivationLink;
use App\Persisters\ActivateAccountPersister;
use App\Persisters\CreateNewTokenPersister;
use App\RequestValidators\ActivateAccountRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivateAccountController
{
    private AccountActivationEmail $accountActivationEmail;

    private AccountActivationLink $accountActivationLink;

    private ActivateAccountPersister $activateAccountPersister;

    private ActivateAccountRequestValidator $activateAccountRequestValidator;

    private CreateNewTokenPersister $createNewTokenPersister;

    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    public function __construct(
        AccountActivationEmail $accountActivationEmail,
        AccountActivationLink $accountActivationLink,
        ActivateAccountPersister $activateAccountPersister,
        ActivateAccountRequestValidator $activateAccountRequestValidator,
        CreateNewTokenPersister $createNewTokenPersister,
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder
    ) {
        $this->accountActivationEmail = $accountActivationEmail;
        $this->accountActivationLink = $accountActivationLink;
        $this->activateAccountPersister = $activateAccountPersister;
        $this->activateAccountRequestValidator = $activateAccountRequestValidator;
        $this->createNewTokenPersister = $createNewTokenPersister;
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * @Route("/-/activate-account", name="activate-account")
     */
    public function index(Request $request): Response
    {
        $requestBody = $request->getContent();
        $data = $this->jsonDecoder->decode($requestBody);

        try {
            $this->activateAccountRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->activateAccountPersister->activateAccount($data);
            return $this->emptySuccessfulResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (TokenExpiredException $e) {
            try {
                $updatedUser = $this->createNewTokenPersister->createNewToken($data);
                $link = $this->accountActivationLink->generateLink($updatedUser->getEmail(), $updatedUser->getToken()->getCode());
                $this->accountActivationEmail->send($updatedUser->getEmail(), $link);
                return $this->errorResponse->createResponse($e->getData());
            } catch (CouldNotPersistException $e) {
                return $this->errorResponse->createResponse($e->getData());
            }
        }
    }
}
