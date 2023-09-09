<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Emails\AccountActivationEmail;
use App\Exceptions\CouldNotPersistException;
use App\Exceptions\RequestValidationException;
use App\Json\JsonDecoder;
use App\Links\AccountActivationLink;
use App\Persisters\RegisterPersister;
use App\RequestValidators\RegisterRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController
{
    private AccountActivationEmail $accountActivationEmail;

    private AccountActivationLink $accountActivationLink;

    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private RegisterPersister $registerPersister;

    private RegisterRequestValidator $registerRequestValidator;

    public function __construct(
        AccountActivationEmail $accountActivationEmail,
        AccountActivationLink $accountActivationLink,
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        RegisterPersister $registerPersister,
        RegisterRequestValidator $registerRequestValidator
    ) {
        $this->accountActivationEmail = $accountActivationEmail;
        $this->accountActivationLink = $accountActivationLink;
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->registerPersister = $registerPersister;
        $this->registerRequestValidator = $registerRequestValidator;
    }

    /**
     * @Route("/-/register", name="register")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->registerRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $user = $this->registerPersister->createUser($data);
            $link = $this->accountActivationLink->generateLink($user->getEmail(), $user->getToken()->getCode());
            $this->accountActivationEmail->send($user->getEmail(), $link);
            return $this->emptySuccessfulResponse->createResponse();
        } catch (CouldNotPersistException | RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
