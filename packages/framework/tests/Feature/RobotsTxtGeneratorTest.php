<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Framework\Features\TextGenerators\RobotsTxtGenerator;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\TextGenerators\RobotsTxtGenerator::class)]
class RobotsTxtGeneratorTest extends TestCase
{
    public function testGeneratesAllowAllRulesetByDefault()
    {
        $this->withoutSiteUrl();

        $this->assertSame("User-agent: *\nAllow: /\n", $this->generate());
    }

    public function testGeneratesSitemapLineWhenSitemapFeatureIsEnabled()
    {
        $this->withSiteUrl();

        $this->assertSame("User-agent: *\nAllow: /\n\nSitemap: https://example.com/sitemap.xml\n", $this->generate());
    }

    public function testOmitsSitemapLineWhenSitemapIsDisabledInConfig()
    {
        $this->withSiteUrl();
        config(['hyde.generate_sitemap' => false]);

        $this->assertSame("User-agent: *\nAllow: /\n", $this->generate());
    }

    public function testGeneratesDisallowRulesFromConfig()
    {
        $this->withoutSiteUrl();
        config(['hyde.robots.disallow' => ['/private', '/admin']]);

        $this->assertSame("User-agent: *\nDisallow: /private\nDisallow: /admin\n", $this->generate());
    }

    public function testDisallowRulesAreWrittenVerbatim()
    {
        $this->withoutSiteUrl();
        config(['hyde.robots.disallow' => ['/*.pdf$', '']]);

        $this->assertSame("User-agent: *\nDisallow: /*.pdf$\nDisallow: \n", $this->generate());
    }

    public function testGeneratesDisallowRulesAndSitemapLineTogether()
    {
        $this->withSiteUrl();
        config(['hyde.robots.disallow' => ['/private']]);

        $this->assertSame("User-agent: *\nDisallow: /private\n\nSitemap: https://example.com/sitemap.xml\n", $this->generate());
    }

    protected function generate(): string
    {
        return (new RobotsTxtGenerator())->generate();
    }
}
