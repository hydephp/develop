<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Framework\Exceptions\InvalidConfigurationException;
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

    public function testNonStringDisallowRuleFailsWithConfigurationException()
    {
        config(['hyde.robots.disallow' => ['/private', 123]]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid `hyde.robots.disallow` entry at index [1]: each Disallow rule must be a string, int given.');

        $this->generate();
    }

    public function testNonStringDisallowRuleExceptionIdentifiesStringKeys()
    {
        config(['hyde.robots.disallow' => ['foo' => null]]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid `hyde.robots.disallow` entry at index [foo]: each Disallow rule must be a string, null given.');

        $this->generate();
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
