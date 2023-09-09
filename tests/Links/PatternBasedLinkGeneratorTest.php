<?php

declare(strict_types=1);

namespace Tests\App\Links;

use App\Links\PatternBasedLinkGenerator;
use PHPUnit\Framework\TestCase;

final class PatternBasedLinkGeneratorTest extends TestCase
{
    public function testGenerateLink(): void
    {
        $linkGenerator = new PatternBasedLinkGenerator();
        $this->assertSame(
            'http://example.com/lorem-ipsum?param1=abc&param2=xyz',
            $linkGenerator->generateLink('http://example.com/lorem-ipsum?param1=%s&param2=%s', 'abc', 'xyz')
        );
    }
}
