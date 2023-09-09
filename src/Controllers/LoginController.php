<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Emails\SecurityCodeEmail;
use App\Exceptions\AuthenticationFailureException;
use App\Exceptions\CouldNotPersistException;
use App\Exceptions\LockAccountException;
use App\Exceptions\RequestValidationException;
use App\Exceptions\SecurityCodeHasToBeGeneratedException;
use App\Json\JsonDecoder;
use App\Persisters\CreateNewSecurityCodePersister;
use App\Persisters\IncrementAuthenticationFailuresPersister;
use App\Persisters\LockAccountPersister;
use App\Persisters\LoginPersister;
use App\RequestValidators\LoginRequestValidator;
use App\Responses\ErrorResponse;
use App\Responses\LoginResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController
{
    private CreateNewSecurityCodePersister $createNewSecurityCodePersister;

    private ErrorResponse $errorResponse;

    private IncrementAuthenticationFailuresPersister $incrementAuthenticationFailuresPersister;

    private JsonDecoder $jsonDecoder;

    private LockAccountPersister $lockAccountPersister;

    private LoginPersister $loginPersister;

    private LoginRequestValidator $loginRequestValidator;

    private LoginResponse $loginResponse;

    private SecurityCodeEmail $securityCodeEmail;

    public function __construct(
        CreateNewSecurityCodePersister $createNewSecurityCodePersister,
        ErrorResponse $errorResponse,
        IncrementAuthenticationFailuresPersister $incrementAuthenticationFailuresPersister,
        JsonDecoder $jsonDecoder,
        LockAccountPersister $lockAccountPersister,
        LoginPersister $loginPersister,
        LoginRequestValidator $loginRequestValidator,
        LoginResponse $loginResponse,
        SecurityCodeEmail $securityCodeEmail
    ) {
        $this->createNewSecurityCodePersister = $createNewSecurityCodePersister;
        $this->errorResponse = $errorResponse;
        $this->incrementAuthenticationFailuresPersister = $incrementAuthenticationFailuresPersister;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockAccountPersister = $lockAccountPersister;
        $this->loginPersister = $loginPersister;
        $this->loginRequestValidator = $loginRequestValidator;
        $this->loginResponse = $loginResponse;
        $this->securityCodeEmail = $securityCodeEmail;
    }

    /**
     * @Route("/-/login", name="login")
     */
    public function index(Request $request): Response
    {
        $requestBody = $request->getContent();
        $data = $this->jsonDecoder->decode($requestBody);

        try {
            $this->loginRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $session = $this->loginPersister->createSession($data);
            return $this->loginResponse->createResponse($session);
        } catch (CouldNotPersistException | RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (LockAccountException $e) {
            $this->lockAccountPersister->lockAccount($data);
            return $this->errorResponse->createResponse($e->getData());
        } catch (AuthenticationFailureException $e) {
            $this->incrementAuthenticationFailuresPersister->incrementAuthenticationFailures($data);
            return $this->errorResponse->createResponse($e->getData());
        } catch (SecurityCodeHasToBeGeneratedException $e) {
            $user = $this->createNewSecurityCodePersister->createNewSecurityCode($data);
            $this->securityCodeEmail->send($user->getEmail(), $user->getSecurityCode()->getCode());
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
