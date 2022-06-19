<?php

namespace Hyde\Testing\Framework\Feature;

use Hyde\Framework\Helpers\Features;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Helpers\Features
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
                $this->assertFalse(Features::$method());
            }
        }
    }

    public function testHasFeatureReturnsTrueWhenFeatureIsEnabled()
    {
        $features = [];
        foreach (get_class_methods(Features::class) as $method) {
            if (! str_starts_with($method, 'has') && $method !== 'enabled') {
                $features[] = '\Hyde\Framework\Helpers\Features::'.$method.'()';
            }
        }

        Config::set('hyde.features', $features);

        foreach ($features as $feature) {
            $this->assertTrue(Features::enabled($feature));
        }
    }
}
