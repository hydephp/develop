<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\YamlConfigurationService;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Services\YamlConfigurationService
 *
 * @see \Hyde\Framework\Testing\Feature\HydeServiceProviderTest as it determines if this service should be booted.
 */
class YamlConfigurationServiceTest extends TestCase
{
    public function test_changes_in_yaml_file_override_changes_in_site_config()
    {
        $this->assertEquals('HydePHP', Config::get('site.name'));
        $this->file('hyde.yml', 'name: Foo');
        YamlConfigurationService::boot();
        $this->assertEquals('Foo', Config::get('site.name'));
    }

    public function test_changes_in_yaml_file_override_changes_in_site_config_when_using_yaml_extension()
    {
        $this->assertEquals('HydePHP', Config::get('site.name'));
        $this->file('hyde.yaml', 'name: Foo');
        YamlConfigurationService::boot();
        $this->assertEquals('Foo', Config::get('site.name'));
    }

    public function test_service_gracefully_handles_missing_file()
    {
        YamlConfigurationService::boot();
        $this->assertEquals('HydePHP', Config::get('site.name'));
    }

    public function test_service_gracefully_handles_empty_file()
    {
        $this->file('hyde.yml', '');
        YamlConfigurationService::boot();
        $this->assertEquals('HydePHP', Config::get('site.name'));
    }
}
