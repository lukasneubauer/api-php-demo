<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Json\JsonDecoder;
use App\Persisters\ResetPasswordPersister;
use App\RequestValidators\ResetPasswordRequestValidator;
use App\Responses\EmptySuccessfulResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController
{
    private EmptySuccessfulResponse $emptySuccessfulResponse;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private ResetPasswordPersister $resetPasswordPersister;

    private ResetPasswordRequestValidator $resetPasswordRequestValidator;

    public function __construct(
        EmptySuccessfulResponse $emptySuccessfulResponse,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        ResetPasswordPersister $resetPasswordPersister,
        ResetPasswordRequestValidator $resetPasswordRequestValidator
    ) {
        $this->emptySuccessfulResponse = $emptySuccessfulResponse;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->resetPasswordPersister = $resetPasswordPersister;
        $this->resetPasswordRequestValidator = $resetPasswordRequestValidator;
    }

    /**
     * @Route("/-/reset-password", name="reset-password")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->resetPasswordRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->resetPasswordPersister->resetPassword($data);
            return $this->emptySuccessfulResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
