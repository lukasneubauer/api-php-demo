<?php

declare(strict_types=1);

namespace App\Responses;

use App\Exceptions\CouldNotGetCurrentRequestFromRequestStackException;
use App\Exceptions\NoApiTokenFoundException;
use App\Http\ApiHeaders;
use App\Http\ApiToken;
use App\Repositories\SessionRepository;
use App\Sessions\ApiTokenRefresher;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Response;

class EmptySuccessfulResponseWithApiToken
{
    private ApiToken $apiToken;

    private ApiTokenRefresher $apiTokenRefresher;

    private ResponseFactory $responseFactory;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        ApiTokenRefresher $apiTokenRefresher,
        ResponseFactory $responseFactory,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->apiTokenRefresher = $apiTokenRefresher;
        $this->responseFactory = $responseFactory;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * @throws CouldNotGetCurrentRequestFromRequestStackException
     * @throws NoApiTokenFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createResponse(): Response
    {
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $refreshedSession = $this->apiTokenRefresher->refreshApiTokenIfExpired($session);

        $response = $this->responseFactory->createResponseInstance();
        $response->headers->set(ApiHeaders::API_TOKEN, $refreshedSession->getCurrentApiToken());
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContent('');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
