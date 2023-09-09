<?php

declare(strict_types=1);

namespace Tests;

use Symfony\Component\HttpFoundation\Request;

final class HttpMethodsDataProvider
{
    private static array $methods = [
        [Request::METHOD_CONNECT],
        [Request::METHOD_DELETE],
        [Request::METHOD_GET],
        // [Request::METHOD_HEAD], not to be bothered with for now
        [Request::METHOD_OPTIONS],
        [Request::METHOD_PATCH],
        [Request::METHOD_POST],
        [Request::METHOD_PURGE],
        [Request::METHOD_PUT],
        [Request::METHOD_TRACE],
    ];

    public static function getHttpMethodsExcludingDelete(): array
    {
        return self::getHttpMethodsExcludingOne(Request::METHOD_DELETE);
    }

    public static function getHttpMethodsExcludingGet(): array
    {
        return self::getHttpMethodsExcludingOne(Request::METHOD_GET);
    }

    public static function getHttpMethodsExcludingPatch(): array
    {
        return self::getHttpMethodsExcludingOne(Request::METHOD_PATCH);
    }

    public static function getHttpMethodsExcludingPost(): array
    {
        return self::getHttpMethodsExcludingOne(Request::METHOD_POST);
    }

    private static function getHttpMethodsExcludingOne(string $methodToExclude): array
    {
        $methods = self::$methods;
        foreach ($methods as $index => $item) {
            if ($item[0] === $methodToExclude) {
                unset($methods[$index]);
                break;
            }
        }

        return $methods;
    }
}
