<?php

declare(strict_types=1);

namespace Tests;

use RuntimeException;

final class ErrorDump
{
    /**
     * @throws RuntimeException
     */
    public static function dumpError(string $error, ?int $statusCode = null): void
    {
        $path = __DIR__ . '/../../var/log';
        $filePath = \realpath($path) . '/error-' . \md5(\microtime()) . '.html';

        \file_put_contents($filePath, $error);

        $isJson = (bool) @\json_decode($error);
        $statusCodeInfo = \is_int($statusCode) ? ' with http status code ' . $statusCode : '';
        $message = 'Test failed' . $statusCodeInfo . '. Error dump was saved in: ' . $filePath . '.';

        if (\stream_isatty(\STDOUT)) {
            $finalError = "\033[01;33m$error\033[00m."; // must be in double quotes to display shell colors
        } else {
            $finalError = $error;
        }

        if ($isJson) {
            $message .= \PHP_EOL . 'The output is also displayed here: ' . $finalError;
        }

        throw new RuntimeException($message);
    }
}
