<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Emails\ForgottenPasswordEmail;
use App\Exceptions\RequestValidationException;
use App\Json\JsonDecoder;
use App\Links\ForgottenPasswordLink;
use App\Persisters\RequestEmailToResetPasswordPersister;
use App\RequestValidators\RequestEmailToResetPasswordRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RequestEmailToResetPasswordController
{
    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private ForgottenPasswordEmail $forgottenPasswordEmail;

    private ForgottenPasswordLink $forgottenPasswordLink;

    private JsonDecoder $jsonDecoder;

    private RequestEmailToResetPasswordPersister $requestEmailToResetPasswordPersister;

    private RequestEmailToResetPasswordRequestValidator $requestEmailToResetPasswordRequestValidator;

    public function __construct(
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        ForgottenPasswordEmail $forgottenPasswordEmail,
        ForgottenPasswordLink $forgottenPasswordLink,
        JsonDecoder $jsonDecoder,
        RequestEmailToResetPasswordPersister $requestEmailToResetPasswordPersister,
        RequestEmailToResetPasswordRequestValidator $requestEmailToResetPasswordRequestValidator
    ) {
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->forgottenPasswordEmail = $forgottenPasswordEmail;
        $this->forgottenPasswordLink = $forgottenPasswordLink;
        $this->jsonDecoder = $jsonDecoder;
        $this->requestEmailToResetPasswordPersister = $requestEmailToResetPasswordPersister;
        $this->requestEmailToResetPasswordRequestValidator = $requestEmailToResetPasswordRequestValidator;
    }

    /**
     * @Route("/-/request-email-to-reset-password", name="request-email-to-reset-password")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->requestEmailToResetPasswordRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $updatedUser = $this->requestEmailToResetPasswordPersister->requestEmailToResetPassword($data);
            $link = $this->forgottenPasswordLink->generateLink($updatedUser->getEmail(), $updatedUser->getToken()->getCode());
            $this->forgottenPasswordEmail->send($updatedUser->getEmail(), $link);
            return $this->emptySuccessfulResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
