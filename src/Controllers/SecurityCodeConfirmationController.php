<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Emails\SecurityCodeEmail;
use App\Exceptions\RequestValidationException;
use App\Exceptions\SecurityCodeConfirmationFailureException;
use App\Exceptions\SecurityCodeExpiredException;
use App\Exceptions\SecurityCodeHasToBeGeneratedAgainException;
use App\Json\JsonDecoder;
use App\Persisters\CreateNewSecurityCodePersister;
use App\Persisters\IncrementSecurityCodeInputFailuresPersister;
use App\Persisters\SecurityCodeConfirmationPersister;
use App\RequestValidators\SecurityCodeConfirmationRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityCodeConfirmationController
{
    private CreateNewSecurityCodePersister $createNewSecurityCodePersister;

    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private IncrementSecurityCodeInputFailuresPersister $incrementSecurityCodeInputFailuresPersister;

    private JsonDecoder $jsonDecoder;

    private SecurityCodeConfirmationPersister $securityCodeConfirmationPersister;

    private SecurityCodeConfirmationRequestValidator $securityCodeConfirmationRequestValidator;

    private SecurityCodeEmail $securityCodeEmail;

    public function __construct(
        CreateNewSecurityCodePersister $createNewSecurityCodePersister,
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        IncrementSecurityCodeInputFailuresPersister $incrementSecurityCodeInputFailuresPersister,
        JsonDecoder $jsonDecoder,
        SecurityCodeConfirmationPersister $securityCodeConfirmationPersister,
        SecurityCodeConfirmationRequestValidator $securityCodeConfirmationRequestValidator,
        SecurityCodeEmail $securityCodeEmail
    ) {
        $this->createNewSecurityCodePersister = $createNewSecurityCodePersister;
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->incrementSecurityCodeInputFailuresPersister = $incrementSecurityCodeInputFailuresPersister;
        $this->jsonDecoder = $jsonDecoder;
        $this->securityCodeConfirmationPersister = $securityCodeConfirmationPersister;
        $this->securityCodeConfirmationRequestValidator = $securityCodeConfirmationRequestValidator;
        $this->securityCodeEmail = $securityCodeEmail;
    }

    /**
     * @Route("/-/security-code-confirmation", name="security-code-confirmation")
     */
    public function index(Request $request): Response
    {
        $requestBody = $request->getContent();
        $data = $this->jsonDecoder->decode($requestBody);

        try {
            $this->securityCodeConfirmationRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->securityCodeConfirmationPersister->confirmSecurityCode($data);
            return $this->emptySuccessfulResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SecurityCodeConfirmationFailureException $e) {
            $this->incrementSecurityCodeInputFailuresPersister->incrementSecurityCodeInputFailures($data);
            return $this->errorResponse->createResponse($e->getData());
        } catch (SecurityCodeExpiredException | SecurityCodeHasToBeGeneratedAgainException $e) {
            $user = $this->createNewSecurityCodePersister->createNewSecurityCode($data);
            $this->securityCodeEmail->send($user->getEmail(), $user->getSecurityCode()->getCode());
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
