<?php

declare(strict_types=1);

namespace App\Responses;

use App\Json\JsonEncoder;
use Symfony\Component\HttpFoundation\Response;

class ErrorResponse
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

    public function createResponse(array $data): Response
    {
        $response = $this->responseFactory->createResponseInstance();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->jsonEncoder->encode($data));
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);

        return $response;
    }
}
