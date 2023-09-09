<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Emails\ForgottenPasswordEmail;
use App\Exceptions\CouldNotPersistException;
use App\Exceptions\RequestValidationException;
use App\Exceptions\TokenExpiredException;
use App\Links\ForgottenPasswordLink;
use App\Persisters\CreateNewTokenPersister;
use App\RequestValidators\CheckUserByEmailAndTokenRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckUserByEmailAndTokenController
{
    private CheckUserByEmailAndTokenRequestValidator $checkUserByEmailAndTokenRequestValidator;

    private CreateNewTokenPersister $createNewTokenPersister;

    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private ForgottenPasswordEmail $forgottenPasswordEmail;

    private ForgottenPasswordLink $forgottenPasswordLink;

    public function __construct(
        CheckUserByEmailAndTokenRequestValidator $checkUserByEmailAndTokenRequestValidator,
        CreateNewTokenPersister $createNewTokenPersister,
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        ForgottenPasswordEmail $forgottenPasswordEmail,
        ForgottenPasswordLink $forgottenPasswordLink
    ) {
        $this->checkUserByEmailAndTokenRequestValidator = $checkUserByEmailAndTokenRequestValidator;
        $this->createNewTokenPersister = $createNewTokenPersister;
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->forgottenPasswordEmail = $forgottenPasswordEmail;
        $this->forgottenPasswordLink = $forgottenPasswordLink;
    }

    /**
     * @Route("/-/check-user-by-email-and-token", name="check-user-by-email-and-token")
     */
    public function index(Request $request): Response
    {
        try {
            $this->checkUserByEmailAndTokenRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $request->query
            );
            return $this->emptySuccessfulResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (TokenExpiredException $e) {
            try {
                $email = $request->query->get('email');
                $updatedUser = $this->createNewTokenPersister->createNewToken(['email' => $email]);
                $link = $this->forgottenPasswordLink->generateLink($updatedUser->getEmail(), $updatedUser->getToken()->getCode());
                $this->forgottenPasswordEmail->send($updatedUser->getEmail(), $link);
                return $this->errorResponse->createResponse($e->getData());
            } catch (CouldNotPersistException $e) {
                return $this->errorResponse->createResponse($e->getData());
            }
        }
    }
}
