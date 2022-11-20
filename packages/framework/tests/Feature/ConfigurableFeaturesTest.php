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
    public function test_has_feature_returns_false_when_feature_is_not_enabled()
    {
        Config::set('hyde.features', []);
        // Foreach method in Features class that begins with "has"
        foreach (get_class_methods(Features::class) as $method) {
            if (str_starts_with($method, 'has')) {
                // Call method and assert false
                $this->assertFalse(Features::$method(), 'Method '.$method.' should return false when feature is not enabled');
            }
        }
    }

    public function test_has_feature_returns_true_when_feature_is_enabled()
    {
        $features = [];
        foreach (get_class_methods(Features::class) as $method) {
            if (! str_starts_with($method, 'has') && $method !== 'enabled') {
                $features[] = '\Hyde\Framework\Helpers\Features::'.$method.'()';
            }
        }

        Config::set('hyde.features', $features);

        foreach ($features as $feature) {
            $this->assertTrue(Features::enabled($feature), 'Method '.$feature.' should return true when feature is enabled');
        }
    }

    public function test_can_generate_sitemap_helper_returns_true_if_hyde_has_base_url()
    {
        config(['site.url' => 'foo']);
        $this->assertTrue(Features::sitemap());
    }

    public function test_can_generate_sitemap_helper_returns_false_if_hyde_does_not_have_base_url()
    {
        config(['site.url' => '']);
        $this->assertFalse(Features::sitemap());
    }

    public function test_can_generate_sitemap_helper_returns_false_if_sitemaps_are_disabled_in_config()
    {
        config(['site.url' => 'foo']);
        config(['site.generate_sitemap' => false]);
        $this->assertFalse(Features::sitemap());
    }

    public function test_to_array_method_returns_method_array()
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

    public function test_to_array_method_contains_all_settings()
    {
        $array = (new Features)->toArray();

        $this->assertArrayHasKey('html-pages', $array);
        $this->assertArrayHasKey('markdown-posts', $array);
        $this->assertArrayHasKey('blade-pages', $array);
        $this->assertArrayHasKey('markdown-pages', $array);
        $this->assertArrayHasKey('documentation-pages', $array);
        $this->assertArrayHasKey('publication-pages', $array);
        $this->assertArrayHasKey('darkmode', $array);
        $this->assertArrayHasKey('documentation-search', $array);
        $this->assertArrayHasKey('torchlight', $array);

        $this->assertCount(10, $array);
    }

    public function test_features_can_be_mocked()
    {
        Features::mock('darkmode', true);
        $this->assertTrue(Features::hasDarkmode());

        Features::mock('darkmode', false);
        $this->assertFalse(Features::hasDarkmode());
    }

    public function test_dynamic_features_can_be_mocked()
    {
        Features::mock('rss', true);
        $this->assertTrue(Features::rss());

        Features::mock('rss', false);
        $this->assertFalse(Features::rss());
    }

    public function test_multiple_features_can_be_mocked()
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
}
