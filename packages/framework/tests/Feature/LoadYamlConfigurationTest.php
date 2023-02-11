<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Services\LoadYamlConfiguration;
use Hyde\Testing\TestCase;

use function config;

/**
 * @covers \Hyde\Foundation\Services\LoadYamlConfiguration
 */
class LoadYamlConfigurationTest extends TestCase
{
    public function test_bootstrapper_applies_yaml_configuration_when_present()
    {
        $this->assertEquals('HydePHP', config('site.name'));
        $this->file('hyde.yml', 'name: Foo');
        $this->app->bootstrapWith([LoadYamlConfiguration::class]);
        $this->assertEquals('Foo', config('site.name'));
    }
}
