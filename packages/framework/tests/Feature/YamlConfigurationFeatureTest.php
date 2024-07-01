<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Env;
use Illuminate\Config\Repository;
use Hyde\Foundation\Internal\LoadYamlConfiguration;
use Illuminate\Support\Facades\Config;
use Hyde\Foundation\Internal\LoadYamlEnvironmentVariables;

/**
 * Test the Yaml configuration feature.
 *
 * @covers \Hyde\Foundation\Internal\LoadYamlConfiguration
 * @covers \Hyde\Foundation\Internal\LoadYamlEnvironmentVariables
 * @covers \Hyde\Foundation\Internal\YamlConfigurationRepository
 */
class YamlConfigurationFeatureTest extends TestCase
{
    protected array $envBackup;

    protected function setUp(): void
    {
        parent::setUp();

        // Backup environment variables that are set in the test
        $this->envBackup = [
            'SITE_NAME' => Env::get('SITE_NAME'),
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Restore environment variables that were set in the test
        foreach ($this->envBackup as $key => $value) {
            if ($value) {
                Env::getRepository()->set($key, $value);
            } else {
                Env::getRepository()->clear($key);
            }
        }
    }

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
        $this->runBootstrappers();

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

        $this->runBootstrappers();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
        $this->assertSame('http://localhost', Config::get('hyde.url'));
        $this->assertSame('My Docs', Config::get('docs.sidebar.header'));
    }

    public function testBootstrapperAppliesYamlConfigurationWhenPresent()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrappers();

        $this->assertSame('Foo', config('hyde.name'));
    }

    public function testChangesInYamlFileOverrideChangesInHydeConfig()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrappers();

        $this->assertSame('Foo', Config::get('hyde.name'));
    }

    public function testChangesInYamlFileOverrideChangesInHydeConfigWhenUsingYamlExtension()
    {
        $this->file('hyde.yaml', 'name: Foo');
        $this->runBootstrappers();

        $this->assertSame('Foo', Config::get('hyde.name'));
    }

    public function testServiceGracefullyHandlesMissingFile()
    {
        $this->runBootstrappers();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
    }

    public function testServiceGracefullyHandlesEmptyFile()
    {
        $this->file('hyde.yml', '');
        $this->runBootstrappers();

        $this->assertSame('HydePHP', Config::get('hyde.name'));
    }

    public function testCanAddArbitraryConfigKeys()
    {
        $this->file('hyde.yml', 'foo: bar');
        $this->runBootstrappers();

        $this->assertSame('bar', Config::get('hyde.foo'));
    }

    public function testConfigurationOptionsAreMerged()
    {
        config(['hyde' => [
            'foo' => 'bar',
            'baz' => 'qux',
        ]]);

        $this->file('hyde.yml', 'baz: hat');
        $this->runBootstrappers();

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

        $this->runBootstrappers();

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

        $this->runBootstrappers();

        $this->assertSame('baz', Config::get('foo.bar'));
    }

    public function testAdditionalNamespacesRequireTheHydeNamespaceToBePresent()
    {
        $this->file('hyde.yml', <<<'YAML'
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

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

        $this->runBootstrappers();

        $this->assertNull(Config::get('foo.bar'));
    }

    public function testHydeNamespaceCanBeEmpty()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

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

        $this->runBootstrappers();

        $this->assertSame('baz', Config::get('foo.bar'));
    }

    public function testHydeNamespaceCanBlank()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde: ''
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

        $this->assertSame('baz', Config::get('foo.bar'));
    }

    public function testDotNotationCanBeUsed()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        foo.bar.baz: qux
        YAML);

        $this->runBootstrappers();

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

        $this->runBootstrappers();

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

        $this->runBootstrappers();

        $this->assertSame('Example Docs', Config::get('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderWhenUsingHydeNamespace()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

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

        $this->runBootstrappers();

        $this->assertSame('Custom', Config::get('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderUnlessAlreadySpecifiedInStandardConfig()
    {
        config(['docs.sidebar.header' => 'Custom']);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Custom', Config::get('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsRssFeedSiteName()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example RSS Feed', Config::get('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameWhenUsingHydeNamespace()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

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

        $this->runBootstrappers();

        $this->assertSame('Custom', Config::get('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameUnlessAlreadySpecifiedInStandardConfig()
    {
        config(['hyde.rss.description' => 'Custom']);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Custom', Config::get('hyde.rss.description'));
    }

    protected function runBootstrappers(): void
    {
        $this->app->bootstrapWith([
            LoadYamlEnvironmentVariables::class,
            LoadYamlConfiguration::class,
        ]);
    }

    protected function refreshConfigRepository(): void
    {
        // Since the environment variables are only evaluated once, we need to refresh the configuration files.

        Config::swap(new Repository([
            'hyde' => require Hyde::path('config/hyde.php'),
            'docs' => require Hyde::path('config/docs.php'),
        ]));
    }
}
