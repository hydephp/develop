<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Features;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Helpers\Features
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
}
