<?php

declare(strict_types=1);

namespace App\Base64;

class Base64Encoder
{
    public function encode(string $sourceString): string
    {
        return \base64_encode($sourceString);
    }
}
