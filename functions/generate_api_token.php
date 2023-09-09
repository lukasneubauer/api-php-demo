<?php

declare(strict_types=1);

use App\Generators\ApiTokenGenerator;

function generate_api_token(): string
{
    return (new ApiTokenGenerator())->generateApiToken();
}
