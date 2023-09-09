<?php

declare(strict_types=1);

namespace Tests;

final class MalformedUuidDataProvider
{
    public static function getMalformedUuids(): array
    {
        return [
            ['1'],
            ['00000000-0000-0000-0000-000000000000'],
            ['00000000-0000-1000-z000-000000000000'],
            ['00000000-0000-a000-0000-000000000000'],
            ['c9358499-5952-9558-9a37-a43b09b8c74e'],
        ];
    }
}
