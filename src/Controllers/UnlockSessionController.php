<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Emails\SecurityCodeEmail;
use App\Exceptions\AuthenticationFailureException;
use App\Exceptions\LockAccountException;
use App\Exceptions\RequestValidationException;
use App\Exceptions\SecurityCodeHasToBeGeneratedException;
use App\Json\JsonDecoder;
use App\Persisters\CreateNewSecurityCodePersister;
use App\Persisters\IncrementAuthenticationFailuresPersister;
use App\Persisters\LockAccountPersister;
use App\Persisters\UnlockSessionPersister;
use App\RequestValidators\UnlockSessionRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnlockSessionController
{
    private CreateNewSecurityCodePersister $createNewSecurityCodePersister;

    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private IncrementAuthenticationFailuresPersister $incrementAuthenticationFailuresPersister;

    private JsonDecoder $jsonDecoder;

    private LockAccountPersister $lockAccountPersister;

    private SecurityCodeEmail $securityCodeEmail;

    private UnlockSessionPersister $unlockSessionPersister;

    private UnlockSessionRequestValidator $unlockSessionRequestValidator;

    public function __construct(
        CreateNewSecurityCodePersister $createNewSecurityCodePersister,
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        IncrementAuthenticationFailuresPersister $incrementAuthenticationFailuresPersister,
        JsonDecoder $jsonDecoder,
        LockAccountPersister $lockAccountPersister,
        SecurityCodeEmail $securityCodeEmail,
        UnlockSessionPersister $unlockSessionPersister,
        UnlockSessionRequestValidator $unlockSessionRequestValidator
    ) {
        $this->createNewSecurityCodePersister = $createNewSecurityCodePersister;
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->incrementAuthenticationFailuresPersister = $incrementAuthenticationFailuresPersister;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockAccountPersister = $lockAccountPersister;
        $this->securityCodeEmail = $securityCodeEmail;
        $this->unlockSessionPersister = $unlockSessionPersister;
        $this->unlockSessionRequestValidator = $unlockSessionRequestValidator;
    }

    /**
     * @Route("/-/unlock-session", name="unlock-session")
     */
    public function index(Request $request): Response
    {
        $requestBody = $request->getContent();
        $data = $this->jsonDecoder->decode($requestBody);

        try {
            $this->unlockSessionRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->unlockSessionPersister->unlockSession($data);
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (LockAccountException $e) {
            $this->lockAccountPersister->lockAccount($data);
            return $this->errorResponse->createResponse($e->getData());
        } catch (AuthenticationFailureException $e) {
            $this->incrementAuthenticationFailuresPersister->incrementAuthenticationFailures($data);
            return $this->errorResponse->createResponse($e->getData());
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SecurityCodeHasToBeGeneratedException $e) {
            $user = $this->createNewSecurityCodePersister->createNewSecurityCode($data);
            $this->securityCodeEmail->send($user->getEmail(), $user->getSecurityCode()->getCode());
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
