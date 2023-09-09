<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CouldNotPersistException;
use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\LockSessionPersister;
use App\Persisters\SettingsPersister;
use App\RequestValidators\SettingsRequestValidator;
use App\Responses\EmptySuccessfulResponseWithApiToken;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController
{
    private EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    private SettingsPersister $settingsPersister;

    private SettingsRequestValidator $settingsRequestValidator;

    public function __construct(
        EmptySuccessfulResponseWithApiToken $emptySuccessfulResponseWithApiToken,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister,
        SettingsPersister $settingsPersister,
        SettingsRequestValidator $settingsRequestValidator
    ) {
        $this->emptySuccessfulResponseWithApiToken = $emptySuccessfulResponseWithApiToken;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
        $this->settingsPersister = $settingsPersister;
        $this->settingsRequestValidator = $settingsRequestValidator;
    }

    /**
     * @Route("/-/settings", name="settings")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->settingsRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $this->settingsPersister->updateSettings($data);
            return $this->emptySuccessfulResponseWithApiToken->createResponse();
        } catch (CouldNotPersistException | RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
