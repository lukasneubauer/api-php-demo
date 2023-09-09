<?php

declare(strict_types=1);

namespace Tests;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class ResponseTester
{
    /**
     * @throws RuntimeException
     */
    public static function assertResponse(int $expectedStatusCode, Response $response): void
    {
        $responseStatusCode = $response->getStatusCode();
        $responseContent = (string) $response->getContent();
        if ($expectedStatusCode !== $responseStatusCode) {
            ErrorDump::dumpError($responseContent, $responseStatusCode);
        }
    }
}
