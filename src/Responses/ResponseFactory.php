<?php

declare(strict_types=1);

namespace App\Responses;

use Symfony\Component\HttpFoundation\Response;

class ResponseFactory
{
    public function createResponseInstance(): Response
    {
        return new Response();
    }
}
