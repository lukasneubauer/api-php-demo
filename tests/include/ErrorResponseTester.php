<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\Assert;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class ErrorResponseTester
{
    /** @var string */
    public const KEY_ERROR = 'error';

    /** @var string */
    public const KEY_CODE = 'code';

    /** @var string */
    public const KEY_MESSAGE = 'message';

    /**
     * @throws RuntimeException
     */
    public static function assertError(
        KernelBrowser $webClient,
        Assert $assert,
        string $method,
        string $uri,
        array $headers,
        string $body,
        int $expectedStatusCode,
        int $expectedApiErrorCode,
        string $expectedApiErrorMessage,
        array $uriParameters = []
    ): void {
        $webClient->request($method, $uri, $uriParameters, [], $headers, $body);
        /** @var Response $response */
        $response = $webClient->getResponse();
        $responseStatusCode = $response->getStatusCode();
        $responseContent = (string) $response->getContent();
        if ($expectedStatusCode !== $responseStatusCode) {
            ErrorDump::dumpError($responseContent, $responseStatusCode);
        }
        $assert->assertTrue($response->headers->has('Content-Type'));
        $assert->assertSame('application/json', $response->headers->get('Content-Type'));
        $data = \json_decode_get_array($responseContent);
        $assert->assertTrue(\is_array($data));
        $assert->assertArrayHasKey(self::KEY_ERROR, $data);
        $error = $data[self::KEY_ERROR];
        $assert->assertArrayHasKey(self::KEY_CODE, $error);
        if ($expectedApiErrorCode !== $error[self::KEY_CODE]) {
            ErrorDump::dumpError($responseContent, $responseStatusCode);
        }
        $assert->assertArrayHasKey(self::KEY_MESSAGE, $error);
        $assert->assertSame($expectedApiErrorMessage, $error[self::KEY_MESSAGE]);
    }
}
