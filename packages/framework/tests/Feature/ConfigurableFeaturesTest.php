<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Facades\Features;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Facades\Features
 */
class ConfigurableFeaturesTest extends TestCase
{
    public function testHasDocumentationSearchReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasDocumentationSearch');
    }

    public function testHasDarkmodeReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasDarkmode');
    }

    public function testHasTorchlightReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasTorchlight');
    }

    public function testHasRssReturnsFalseWhenFeatureIsNotEnabled()
    {
        $this->expectMethodReturnsFalse('hasRss');
    }

    public function testHasDarkmodeReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasDarkmode');
    }

    public function testHasSitemapReturnsTrueWhenFeatureIsEnabled()
    {
        $this->expectMethodReturnsTrue('hasSitemap');
    }

    public function testCanGenerateSitemapHelperReturnsTrueIfHydeHasBaseUrl()
    {
        config(['hyde.url' => 'foo']);
        $this->assertTrue(Features::hasSitemap());
    }

    public function testCanGenerateSitemapHelperReturnsFalseIfHydeDoesNotHaveBaseUrl()
    {
        config(['hyde.url' => '']);
        $this->assertFalse(Features::hasSitemap());
    }

    public function testCanGenerateSitemapHelperReturnsFalseIfSitemapsAreDisabledInConfig()
    {
        config(['hyde.url' => 'foo']);
        config(['hyde.generate_sitemap' => false]);
        $this->assertFalse(Features::hasSitemap());
    }

    public function testToArrayMethodContainsAllSettings()
    {
        $this->assertSame([
            'html-pages' => true,
            'markdown-posts' => true,
            'blade-pages' => true,
            'markdown-pages' => true,
            'documentation-pages' => true,
            'darkmode' => true,
            'documentation-search' => true,
            'torchlight' => true,
        ], (new Features)->toArray());
    }

    public function testToArrayMethodContainsAllSettingsIncludingFalseValues()
    {
        config(['hyde.features' => [
            Features::htmlPages(),
            Features::markdownPosts(),
            Features::bladePages(),
        ]]);

        $this->assertSame([
            'html-pages' => true,
            'markdown-posts' => true,
            'blade-pages' => true,
            'markdown-pages' => false,
            'documentation-pages' => false,
            'darkmode' => false,
            'documentation-search' => false,
            'torchlight' => false,
        ], (new Features)->toArray());
    }

    public function testFeaturesCanBeMocked()
    {
        Features::mock('darkmode', true);
        $this->assertTrue(Features::hasDarkmode());

        Features::mock('darkmode', false);
        $this->assertFalse(Features::hasDarkmode());
    }

    public function testMultipleFeaturesCanBeMocked()
    {
        Features::mock([
            'blade-pages' => true,
            'darkmode' => true,
        ]);

        $this->assertTrue(Features::hasBladePages());
        $this->assertTrue(Features::hasDarkmode());

        Features::mock([
            'blade-pages' => false,
            'darkmode' => false,
        ]);

        $this->assertFalse(Features::hasBladePages());
        $this->assertFalse(Features::hasDarkmode());
    }

    public function testGetEnabledUsesDefaultOptionsByDefault()
    {
        $default = $this->defaultOptions();

        $this->assertSame($default, Features::enabled());
    }

    public function testGetEnabledUsesDefaultOptionsWhenConfigIsEmpty()
    {
        config(['hyde' => []]);

        $default = $this->defaultOptions();

        $this->assertSame($default, Features::enabled());
    }

    public function testGetEnabledUsesConfiguredOptions()
    {
        config(['hyde.features' => [
            Features::htmlPages(),
            Features::markdownPosts(),
            Features::bladePages(),
        ]]);

        $this->assertSame([
            'html-pages',
            'markdown-posts',
            'blade-pages',
        ], Features::enabled());
    }

    public function testCannotUseArbitraryValuesInEnabledOptions()
    {
        $config = [
            Features::htmlPages(),
            Features::markdownPosts(),
            Features::bladePages(),
            'foo',
        ];

        config(['hyde.features' => $config]);

        $this->assertSame([
            'html-pages',
            'markdown-posts',
            'blade-pages',
        ], Hyde::features()->enabled());
    }

    protected function defaultOptions(): array
    {
        return [
            'html-pages',
            'markdown-posts',
            'blade-pages',
            'markdown-pages',
            'documentation-pages',
            'darkmode',
            'documentation-search',
            'torchlight',
        ];
    }

    protected function expectMethodReturnsFalse(string $method): void
    {
        Config::set('hyde.features', []);

        $this->assertFalse(Features::$method(), "Method '$method' should return false when feature is not enabled");
    }

    protected function expectMethodReturnsTrue(string $method): void
    {
        $this->assertTrue(Features::$method(), "Method '$method' should return true when feature is enabled");
    }
}
