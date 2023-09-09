<?php

declare(strict_types=1);

namespace App\Json;

class JsonDecoder
{
    public function decode(string $json): ?array
    {
        return @\json_decode($json, true);
    }
}
