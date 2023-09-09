<?php

declare(strict_types=1);

namespace App\Responses;

use App\Http\ApiHeaders;
use App\Http\ApiToken;
use App\Json\JsonEncoder;
use App\Repositories\SessionRepository;
use App\Sessions\ApiTokenRefresher;
use Symfony\Component\HttpFoundation\Response;

class ProfileResponse
{
    private ApiToken $apiToken;

    private ApiTokenRefresher $apiTokenRefresher;

    private JsonEncoder $jsonEncoder;

    private ResponseFactory $responseFactory;

    private SessionRepository $sessionRepository;

    public function __construct(
        ApiToken $apiToken,
        ApiTokenRefresher $apiTokenRefresher,
        JsonEncoder $jsonEncoder,
        ResponseFactory $responseFactory,
        SessionRepository $sessionRepository
    ) {
        $this->apiToken = $apiToken;
        $this->apiTokenRefresher = $apiTokenRefresher;
        $this->jsonEncoder = $jsonEncoder;
        $this->responseFactory = $responseFactory;
        $this->sessionRepository = $sessionRepository;
    }

    public function createResponse(): Response
    {
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();

        $data = [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'avatar' => $user->getAvatar(),
            'email' => $user->getEmail(),
            'isTeacher' => $user->isTeacher(),
            'isStudent' => $user->isStudent(),
            'timezone' => $user->getTimezone(),
            'isActive' => $user->isActive(),
        ];

        $refreshedSession = $this->apiTokenRefresher->refreshApiTokenIfExpired($session);

        $response = $this->responseFactory->createResponseInstance();
        $response->headers->set(ApiHeaders::API_TOKEN, $refreshedSession->getCurrentApiToken());
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->jsonEncoder->encode($data));
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
