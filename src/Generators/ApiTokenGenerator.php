<?php

declare(strict_types=1);

namespace App\Generators;

use App\Http\ApiHeaders;
use Nette\Utils\Random;

class ApiTokenGenerator
{
    public function generateApiToken(): string
    {
        return Random::generate(ApiHeaders::API_TOKEN_LENGTH, ApiHeaders::API_TOKEN_PATTERN);
    }
}
