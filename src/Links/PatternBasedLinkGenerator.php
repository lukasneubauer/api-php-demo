<?php

declare(strict_types=1);

namespace App\Links;

class PatternBasedLinkGenerator
{
    public function generateLink(string $linkPattern, string $email, string $token): string
    {
        return \sprintf($linkPattern, $email, $token);
    }
}
