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
    public function testCanDefineHydeConfigSettingsInHydeYmlFile()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        name: HydePHP
        url: "http://localhost"
        pretty_urls: false
        generate_sitemap: true
        rss:
          enabled: true
          filename: feed.xml
          description: HydePHP RSS Feed
        language: en
        output_directory: _site
        YAML);
        $this->runBootstrapper();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
        $this->assertSame('http://localhost', Config::get('hyde.url'));
        $this->assertSame(false, Config::get('hyde.pretty_urls'));
        $this->assertSame(true, Config::get('hyde.generate_sitemap'));
        $this->assertSame(true, Config::get('hyde.rss.enabled'));
        $this->assertSame('feed.xml', Config::get('hyde.rss.filename'));
        $this->assertSame('HydePHP RSS Feed', Config::get('hyde.rss.description'));
        $this->assertSame('en', Config::get('hyde.language'));
        $this->assertSame('_site', Config::get('hyde.output_directory'));
    }

    public function testBootstrapperAppliesYamlConfigurationWhenPresent()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrapper();

        $this->assertSame('Foo', config('hyde.name'));
    }

    public function testChangesInYamlFileOverrideChangesInHydeConfig()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrapper();

        $this->assertSame('Foo', Config::get('hyde.name'));
    }

    public function testChangesInYamlFileOverrideChangesInHydeConfigWhenUsingYamlExtension()
    {
        $this->file('hyde.yaml', 'name: Foo');
        $this->runBootstrapper();

        $this->assertSame('Foo', Config::get('hyde.name'));
    }

    public function testServiceGracefullyHandlesMissingFile()
    {
        $this->runBootstrapper();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
    }

    public function testServiceGracefullyHandlesEmptyFile()
    {
        $this->file('hyde.yml', '');
        $this->runBootstrapper();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
    }

    public function testCanAddArbitraryConfigKeys()
    {
        $this->file('hyde.yml', 'foo: bar');
        $this->runBootstrapper();

        $this->assertSame('bar', Config::get('hyde.foo'));
    }

    public function testConfigurationOptionsAreMerged()
    {
        config(['hyde' => [
            'foo' => 'bar',
            'baz' => 'qux',
        ]]);

        $this->file('hyde.yml', 'baz: hat');
        $this->runBootstrapper();

        $this->assertSame('bar', Config::get('hyde.foo'));
    }

    public function testCanAddConfigurationOptionsInNamespacedArray()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
          name: HydePHP
          foo: bar
          bar:
            baz: qux
        YAML);
        $this->runBootstrapper();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
        $this->assertSame('bar', Config::get('hyde.foo'));
        $this->assertSame('qux', Config::get('hyde.bar.baz'));
    }

    public function testCanAddArbitraryNamespacedData()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
          some: thing 
        foo:
          bar: baz
        YAML);
        $this->runBootstrapper();

        $this->assertSame('baz', Config::get('foo.bar'));
    }

    public function testAdditionalNamespacesRequireTheHydeNamespaceToBePresent()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        foo:
          bar: baz
        YAML);
        $this->runBootstrapper();

        $this->assertNull(Config::get('foo.bar'));
    }

    public function testAdditionalNamespacesRequiresHydeNamespaceToBeTheFirstEntry()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        foo:
          bar: baz
        hyde:
          some: thing
        YAML);
        $this->runBootstrapper();

        $this->assertNull(Config::get('foo.bar'));
    }

    protected function runBootstrapper(): void
    {
        $this->app->bootstrapWith([LoadYamlConfiguration::class]);
    }
}
