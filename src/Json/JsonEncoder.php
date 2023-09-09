<?php

declare(strict_types=1);

namespace App\Json;

class JsonEncoder
{
    public function encode(array $data): string
    {
        return (string) @\json_encode($data);
    }
}
