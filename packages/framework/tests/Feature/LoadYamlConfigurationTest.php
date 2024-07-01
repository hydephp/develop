<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Foundation\Internal\LoadYamlConfiguration;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Foundation\Internal\LoadYamlConfiguration
 */
class LoadYamlConfigurationTest extends TestCase
{
    public function testCanDefineHydeConfigSettingsInHydeYmlFile()
    {
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

    public function testCanDefineMultipleConfigSettingsInHydeYmlFile()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: HydePHP
            url: "http://localhost"
        docs:
            sidebar:
                header: "My Docs"
        YAML);

        $this->runBootstrapper();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
        $this->assertSame('http://localhost', Config::get('hyde.url'));
        $this->assertSame('My Docs', Config::get('docs.sidebar.header'));
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
        $this->file('hyde.yml', <<<'YAML'
        foo:
          bar: baz
        YAML);

        $this->runBootstrapper();

        $this->assertNull(Config::get('foo.bar'));
    }

    public function testAdditionalNamespacesRequiresHydeNamespaceToBeTheFirstEntry()
    {
        $this->file('hyde.yml', <<<'YAML'
        foo:
          bar: baz
        hyde:
          some: thing
        YAML);

        $this->runBootstrapper();

        $this->assertNull(Config::get('foo.bar'));
    }

    public function testHydeNamespaceCanBeEmpty()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
        foo:
          bar: baz
        YAML);

        $this->runBootstrapper();

        $this->assertSame('baz', Config::get('foo.bar'));
    }

    public function testHydeNamespaceCanBeNull()
    {
        // This is essentially the same as the empty state test above, at least according to the YAML spec.
        $this->file('hyde.yml', <<<'YAML'
        hyde: null
        foo:
          bar: baz
        YAML);

        $this->runBootstrapper();

        $this->assertSame('baz', Config::get('foo.bar'));
    }

    public function testHydeNamespaceCanBlank()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde: ''
        foo:
          bar: baz
        YAML);

        $this->runBootstrapper();

        $this->assertSame('baz', Config::get('foo.bar'));
    }

    public function testDotNotationCanBeUsed()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        foo.bar.baz: qux
        YAML);

        $this->runBootstrapper();

        $this->assertSame(['foo' => ['bar' => ['baz' => 'qux']]], Config::get('hyde'));
        $this->assertSame('qux', Config::get('hyde.foo.bar.baz'));
    }

    public function testDotNotationCanBeUsedWithNamespaces()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
            foo.bar.baz: qux
        one:
            foo:
                bar:
                    baz: qux
        two:
            foo.bar.baz: qux
        YAML);

        $this->runBootstrapper();

        $expected = ['foo' => ['bar' => ['baz' => 'qux']]];

        $this->assertSame($expected, Config::get('hyde'));
        $this->assertSame($expected, Config::get('one'));
        $this->assertSame($expected, Config::get('two'));
    }

    public function testSettingSiteNameSetsSidebarHeader()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Example Docs', Config::get('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderWhenUsingHydeNamespace()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Example Docs', Config::get('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderUnlessAlreadySpecifiedInYamlConfig()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        docs:
            sidebar:
                header: Custom
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Custom', Config::get('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderUnlessAlreadySpecifiedInStandardConfig()
    {
        config(['docs.sidebar.header' => 'Custom']);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Custom', Config::get('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsRssFeedSiteName()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Example RSS Feed', Config::get('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameWhenUsingHydeNamespace()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Example RSS Feed', Config::get('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameUnlessAlreadySpecifiedInYamlConfig()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
            rss:
                description: Custom
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Custom', Config::get('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameUnlessAlreadySpecifiedInStandardConfig()
    {
        config(['hyde.rss.description' => 'Custom']);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrapper();

        $this->assertSame('Custom', Config::get('hyde.rss.description'));
    }

    protected function runBootstrapper(): void
    {
        $this->app->bootstrapWith([LoadYamlConfiguration::class]);
    }
}
