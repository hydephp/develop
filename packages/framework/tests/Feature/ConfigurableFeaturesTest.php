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
        // Foreach method in Features class that begins with "has"
        foreach (get_class_methods(Features::class) as $method) {
            if (str_starts_with($method, 'has')) {
                // Call method and assert false
                $this->assertFalse(Features::$method(), 'Method '.$method.' should return false when feature is not enabled');
            }
        }
    }

    public function testHasFeatureReturnsTrueWhenFeatureIsEnabled()
    {
        $features = [];
        foreach (get_class_methods(Features::class) as $method) {
            if (str_starts_with($method, 'has') && $method !== 'hasDocumentationSearch' && $method !== 'hasTorchlight') {
                $features[] = $method;
            }
        }

        foreach ($features as $method) {
            $this->assertTrue(Features::$method(), 'Method '.$method.' should return true when feature is enabled');
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

    public function testHasThemeToggleButtonsReturnsTrueWhenDarkmodeEnabledAndConfigTrue()
    {
        // Enable dark mode and set hyde.theme_toggle_buttons config option to true
        Features::mock('darkmode', true);
        config(['hyde.theme_toggle_buttons' => true]);

        $this->assertTrue(Features::hasThemeToggleButtons());
    }

    public function testHasThemeToggleButtonsReturnsFalseWhenDarkmodeDisabled()
    {
        // Disable dark mode
        Features::mock('darkmode', false);
        // It doesn't matter what the config value is here

        $this->assertFalse(Features::hasThemeToggleButtons());
    }

    public function testHasThemeToggleButtonsReturnsFalseWhenConfigFalse()
    {
        // Enable dark mode
        Features::mock('darkmode', true);
        // Set hyde.theme_toggle_buttons config option to false
        config(['hyde.theme_toggle_buttons' => false]);

        $this->assertFalse(Features::hasThemeToggleButtons());
    }

    public function testHasThemeToggleButtonsReturnsTrueWhenDarkmodeEnabledAndConfigNotSet()
    {
        // Enable dark mode
        Features::mock('darkmode', true);
        // Config option not set, default value assumed to be true

        $this->assertTrue(Features::hasThemeToggleButtons());
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
        $this->assertArrayHasKey('theme-toggle-buttons', $array);
        $this->assertArrayHasKey('documentation-search', $array);
        $this->assertArrayHasKey('torchlight', $array);

        $this->assertCount(9, $array);
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
        Features::mock('rss', true);
        Features::mock('darkmode', true);

        $this->assertTrue(Features::rss());
        $this->assertTrue(Features::hasDarkmode());

        Features::mock('rss', false);
        Features::mock('darkmode', false);

        $this->assertFalse(Features::rss());
        $this->assertFalse(Features::hasDarkmode());
    }
}
