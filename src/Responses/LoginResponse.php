<?php

declare(strict_types=1);

namespace App\Responses;

use App\Entities\Session;
use App\Json\JsonEncoder;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse
{
    private JsonEncoder $jsonEncoder;

    private ResponseFactory $responseFactory;

    public function __construct(
        JsonEncoder $jsonEncoder,
        ResponseFactory $responseFactory
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->responseFactory = $responseFactory;
    }

    public function createResponse(Session $session): Response
    {
        $data = ['apiToken' => $session->getCurrentApiToken()];

        $response = $this->responseFactory->createResponseInstance();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->jsonEncoder->encode($data));
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
