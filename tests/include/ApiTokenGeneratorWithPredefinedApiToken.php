<?php

declare(strict_types=1);

namespace Tests;

use App\Generators\ApiTokenGenerator;

final class ApiTokenGeneratorWithPredefinedApiToken extends ApiTokenGenerator
{
    public function generateApiToken(): string
    {
        return 'p1tnyxrxmh1f2egbi0cywuuey64y47a3o0ifuse05dbwrjfm7vrv02yg76519kln5280bdiau7niik9s';
    }
}
