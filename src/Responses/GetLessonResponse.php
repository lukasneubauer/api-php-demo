<?php

declare(strict_types=1);

namespace App\Responses;

use App\DateTime\UTCToGivenTimezone;
use App\Entities\Lesson;
use App\Http\ApiHeaders;
use App\Http\ApiToken;
use App\Json\JsonEncoder;
use App\Repositories\SessionRepository;
use App\Sessions\ApiTokenRefresher;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class GetLessonResponse
{
    private ApiToken $apiToken;

    private ApiTokenRefresher $apiTokenRefresher;

    private JsonEncoder $jsonEncoder;

    private ResponseFactory $responseFactory;

    private SessionRepository $sessionRepository;

    private UTCToGivenTimezone $utcToGivenTimezone;

    public function __construct(
        ApiToken $apiToken,
        ApiTokenRefresher $apiTokenRefresher,
        JsonEncoder $jsonEncoder,
        ResponseFactory $responseFactory,
        SessionRepository $sessionRepository,
        UTCToGivenTimezone $utcToGivenTimezone
    ) {
        $this->apiToken = $apiToken;
        $this->apiTokenRefresher = $apiTokenRefresher;
        $this->jsonEncoder = $jsonEncoder;
        $this->responseFactory = $responseFactory;
        $this->sessionRepository = $sessionRepository;
        $this->utcToGivenTimezone = $utcToGivenTimezone;
    }

    /**
     * @throws Exception
     */
    public function createResponse(Lesson $lesson): Response
    {
        $apiToken = $this->apiToken->getApiToken();
        $session = $this->sessionRepository->getByApiToken($apiToken);
        $user = $session->getUser();
        $timezone = $user->getTimezone();

        $from = $this->utcToGivenTimezone->convertUTCToGivenTimezone(
            $timezone,
            $lesson->getFrom()->format('Y-m-d H:i:s')
        );

        $to = $this->utcToGivenTimezone->convertUTCToGivenTimezone(
            $timezone,
            $lesson->getTo()->format('Y-m-d H:i:s')
        );

        $data = [
            'id' => $lesson->getId(),
            'name' => $lesson->getName(),
            'from' => $from->format('Y-m-d H:i:s'),
            'to' => $to->format('Y-m-d H:i:s'),
            'course' => [
                'id' => $lesson->getCourse()->getId(),
                'name' => $lesson->getCourse()->getName(),
                'subject' => [
                    'id' => $lesson->getCourse()->getSubject()->getId(),
                    'name' => $lesson->getCourse()->getSubject()->getName(),
                ],
            ],
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
