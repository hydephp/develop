<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\Internal\LoadYamlConfiguration;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

use function config;

/**
 * @covers \Hyde\Foundation\Internal\LoadYamlConfiguration
 */
class LoadYamlConfigurationTest extends TestCase
{
    public function test_bootstrapper_applies_yaml_configuration_when_present()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrapper();

        $this->assertSame('Foo', config('hyde.name'));
    }

    public function test_changes_in_yaml_file_override_changes_in_hyde_config()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrapper();

        $this->assertSame('Foo', Config::get('hyde.name'));
    }

    public function test_changes_in_yaml_file_override_changes_in_hyde_config_when_using_yaml_extension()
    {
        $this->file('hyde.yaml', 'name: Foo');
        $this->runBootstrapper();

        $this->assertSame('Foo', Config::get('hyde.name'));
    }

    public function test_service_gracefully_handles_missing_file()
    {
        $this->runBootstrapper();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
    }

    public function test_service_gracefully_handles_empty_file()
    {
        $this->file('hyde.yml', '');
        $this->runBootstrapper();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
    }

    protected function runBootstrapper(): void
    {
        $this->app->bootstrapWith([LoadYamlConfiguration::class]);
    }
}
