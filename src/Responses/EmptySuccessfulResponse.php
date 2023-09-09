<?php

declare(strict_types=1);

namespace App\Responses;

use Symfony\Component\HttpFoundation\Response;

class EmptySuccessfulResponse
{
    private ResponseFactory $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function createResponse(): Response
    {
        $response = $this->responseFactory->createResponseInstance();
        $response->headers->set('Content-Type', 'text/plain');
        $response->setContent('');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
