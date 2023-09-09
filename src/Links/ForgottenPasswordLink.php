<?php

declare(strict_types=1);

namespace App\Links;

class ForgottenPasswordLink
{
    private PatternBasedLinkGenerator $linkGenerator;

    private string $linkPattern;

    public function __construct(PatternBasedLinkGenerator $linkGenerator, string $linkPattern)
    {
        $this->linkGenerator = $linkGenerator;
        $this->linkPattern = $linkPattern;
    }

    public function generateLink(string $email, string $token): string
    {
        return $this->linkGenerator->generateLink($this->linkPattern, $email, $token);
    }
}
