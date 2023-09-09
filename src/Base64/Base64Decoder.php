<?php

declare(strict_types=1);

namespace App\Base64;

class Base64Decoder
{
    public function decode(string $base64String): string
    {
        return (string) \base64_decode($base64String, true);
    }
}
