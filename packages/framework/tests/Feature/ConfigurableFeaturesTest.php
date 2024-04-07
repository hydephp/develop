<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Features;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Facades\Features
 */
class ConfigurableFeaturesTest extends TestCase
{
    public function testHasFeatureReturnsFalseWhenFeatureIsNotEnabled()
    {
        Config::set('hyde.features', []);

        foreach (get_class_methods(Features::class) as $method) {
            if (str_starts_with($method, 'has')) {
                $this->assertFalse(Features::$method(), 'Method '.$method.' should return false when feature is not enabled');
            }
        }
    }

    public function testHasFeatureReturnsTrueWhenFeatureIsEnabled()
    {
        foreach (get_class_methods(Features::class) as $method) {
            if (str_starts_with($method, 'has') && $method !== 'hasDocumentationSearch' && $method !== 'hasTorchlight') {
                $this->assertTrue(Features::$method(), 'Method '.$method.' should return false when feature is not enabled');
            }
        }
    }

    public function testCanGenerateSitemapHelperReturnsTrueIfHydeHasBaseUrl()
    {
        config(['hyde.url' => 'foo']);
        $this->assertTrue(Features::sitemap());
    }

    public function testCanGenerateSitemapHelperReturnsFalseIfHydeDoesNotHaveBaseUrl()
    {
        config(['hyde.url' => '']);
        $this->assertFalse(Features::sitemap());
    }

    public function testCanGenerateSitemapHelperReturnsFalseIfSitemapsAreDisabledInConfig()
    {
        config(['hyde.url' => 'foo']);
        config(['hyde.generate_sitemap' => false]);
        $this->assertFalse(Features::sitemap());
    }

    public function testToArrayMethodReturnsMethodArray()
    {
        $array = (new Features)->toArray();
        $this->assertIsArray($array);
        $this->assertNotEmpty($array);
        foreach ($array as $feature => $enabled) {
            $this->assertIsString($feature);
            $this->assertIsBool($enabled);
            $this->assertStringStartsNotWith('has', $feature);
        }
    }

    public function testToArrayMethodContainsAllSettings()
    {
        $array = (new Features)->toArray();

        $this->assertArrayHasKey('html-pages', $array);
        $this->assertArrayHasKey('markdown-posts', $array);
        $this->assertArrayHasKey('blade-pages', $array);
        $this->assertArrayHasKey('markdown-pages', $array);
        $this->assertArrayHasKey('documentation-pages', $array);
        $this->assertArrayHasKey('darkmode', $array);
        $this->assertArrayHasKey('documentation-search', $array);
        $this->assertArrayHasKey('torchlight', $array);

        $this->assertCount(8, $array);
    }

    public function testFeaturesCanBeMocked()
    {
        Features::mock('darkmode', true);
        $this->assertTrue(Features::hasDarkmode());

        Features::mock('darkmode', false);
        $this->assertFalse(Features::hasDarkmode());
    }

    public function testDynamicFeaturesCanBeMocked()
    {
        Features::mock('rss', true);
        $this->assertTrue(Features::rss());

        Features::mock('rss', false);
        $this->assertFalse(Features::rss());
    }

    public function testMultipleFeaturesCanBeMocked()
    {
        Features::mock([
            'rss' => true,
            'darkmode' => true,
        ]);

        $this->assertTrue(Features::rss());
        $this->assertTrue(Features::hasDarkmode());

        Features::mock([
            'rss' => false,
            'darkmode' => false,
        ]);

        $this->assertFalse(Features::rss());
        $this->assertFalse(Features::hasDarkmode());
    }

    public function testGetEnabledUsesDefaultOptionsByDefault()
    {
        $features = new Features();

        $default = $this->defaultOptions();

        $this->assertSame($default, $features->getFeatures());
    }

    public function testGetEnabledUsesDefaultOptionsWhenConfigIsEmpty()
    {
        config(['hyde' => []]);

        $features = new Features();

        $default = $this->defaultOptions();

        $this->assertSame($default, $features->getFeatures());
    }

    public function testGetEnabledUsesConfiguredOptions()
    {
        $config = [
            Features::htmlPages(),
            Features::markdownPosts(),
            Features::bladePages(),
        ];
        $expected = [
            Features::htmlPages() => true,
            Features::markdownPosts() => true,
            Features::bladePages() => true,
            'markdown-pages' => false,
            'documentation-pages' => false,
            'darkmode' => false,
            'documentation-search' => false,
            'torchlight' => false,
        ];

        config(['hyde.features' => $config]);

        $features = new Features();
        $this->assertSame($expected, $features->getFeatures());
    }

    public function testCannotUseArbitraryValuesInEnabledOptions()
    {
        $this->markTestSkipped('Todo: Implement if it is worth the complexity.');

        $config = [
            Features::htmlPages(),
            Features::markdownPosts(),
            Features::bladePages(),
            'foo',
        ];

        config(['hyde.features' => $config]);

        $features = new Features();
        $this->assertSame(array_slice($config, 0, 3), $features->getFeatures());
    }

    protected function defaultOptions(): array
    {
        return [
            'html-pages' => true,
            'markdown-posts' => true,
            'blade-pages' => true,
            'markdown-pages' => true,
            'documentation-pages' => true,
            'darkmode' => true,
            'documentation-search' => true,
            'torchlight' => true,
        ];
    }
}
