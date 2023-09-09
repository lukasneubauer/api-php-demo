<?php

declare(strict_types=1);

use App\Json\JsonDecoder;

function json_decode_get_array(string $json): ?array
{
    return (new JsonDecoder())->decode($json);
}
