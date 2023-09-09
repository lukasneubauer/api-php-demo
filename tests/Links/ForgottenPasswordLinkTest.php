<?php

declare(strict_types=1);

namespace Tests\App\Links;

use App\Links\ForgottenPasswordLink;
use App\Links\PatternBasedLinkGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class ForgottenPasswordLinkTest extends TestCase
{
    public function testGenerateLink(): void
    {
        $expectedLink = 'http://example.com/lorem-ipsum?param1=abc&param2=xyz';
        $patternBasedLinkGenerator = m::mock(PatternBasedLinkGenerator::class)
            ->shouldReceive('generateLink')
            ->times(1)
            ->andReturn($expectedLink)
            ->getMock();
        $linkPattern = 'http://example.com/lorem-ipsum?param1=%s&param2=%s';
        $link = new ForgottenPasswordLink($patternBasedLinkGenerator, $linkPattern);
        $this->assertSame(
            $expectedLink,
            $link->generateLink('abc', 'xyz')
        );
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
