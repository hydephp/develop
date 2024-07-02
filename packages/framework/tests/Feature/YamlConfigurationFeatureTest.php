<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Foundation\Internal\LoadConfiguration;
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
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we are using the real config repository.
        Config::swap(app()->make('config'));
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

        $this->assertSame('HydePHP', $this->getConfig('hyde.name'));
        $this->assertSame('http://localhost', $this->getConfig('hyde.url'));
        $this->assertSame(false, $this->getConfig('hyde.pretty_urls'));
        $this->assertSame(true, $this->getConfig('hyde.generate_sitemap'));
        $this->assertSame(true, $this->getConfig('hyde.rss.enabled'));
        $this->assertSame('feed.xml', $this->getConfig('hyde.rss.filename'));
        $this->assertSame('HydePHP RSS Feed', $this->getConfig('hyde.rss.description'));
        $this->assertSame('en', $this->getConfig('hyde.language'));
        $this->assertSame('_site', $this->getConfig('hyde.output_directory'));
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

        $this->assertSame('HydePHP', $this->getConfig('hyde.name'));
        $this->assertSame('http://localhost', $this->getConfig('hyde.url'));
        $this->assertSame('My Docs', $this->getConfig('docs.sidebar.header'));
    }

    public function testBootstrapperAppliesYamlConfigurationWhenPresent()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrappers();

        $this->assertSame('Foo', $this->getConfig('hyde.name'));
    }

    public function testChangesInYamlFileOverrideChangesInHydeConfig()
    {
        $this->file('hyde.yml', 'name: Foo');
        $this->runBootstrappers();

        $this->assertSame('Foo', $this->getConfig('hyde.name'));
    }

    public function testChangesInYamlFileOverrideChangesInHydeConfigWhenUsingYamlExtension()
    {
        $this->file('hyde.yaml', 'name: Foo');
        $this->runBootstrappers();

        $this->assertSame('Foo', $this->getConfig('hyde.name'));
    }

    public function testServiceGracefullyHandlesMissingFile()
    {
        $this->runBootstrappers();

        $this->assertSame('HydePHP', $this->getConfig('hyde.name'));
    }

    public function testServiceGracefullyHandlesEmptyFile()
    {
        $this->file('hyde.yml', '');
        $this->runBootstrappers();

        $this->assertSame('HydePHP', $this->getConfig('hyde.name'));
    }

    public function testCanAddArbitraryConfigKeys()
    {
        $this->file('hyde.yml', 'foo: bar');
        $this->runBootstrappers();

        $this->assertSame('bar', $this->getConfig('hyde.foo'));
    }

    public function testConfigurationOptionsAreMerged()
    {
        config(['hyde' => [
            'foo' => 'bar',
            'baz' => 'qux',
        ]]);

        $this->file('hyde.yml', 'baz: hat');
        $this->runBootstrappers();

        $this->assertSame('bar', $this->getConfig('hyde.foo'));
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

        $this->assertSame('HydePHP', $this->getConfig('hyde.name'));
        $this->assertSame('bar', $this->getConfig('hyde.foo'));
        $this->assertSame('qux', $this->getConfig('hyde.bar.baz'));
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

        $this->assertSame('baz', $this->getConfig('foo.bar'));
    }

    public function testAdditionalNamespacesRequireTheHydeNamespaceToBePresent()
    {
        $this->file('hyde.yml', <<<'YAML'
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

        $this->assertNull($this->getConfig('foo.bar'));
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

        $this->assertNull($this->getConfig('foo.bar'));
    }

    public function testHydeNamespaceCanBeEmpty()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

        $this->assertSame('baz', $this->getConfig('foo.bar'));
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

        $this->assertSame('baz', $this->getConfig('foo.bar'));
    }

    public function testHydeNamespaceCanBlank()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde: ''
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

        $this->assertSame('baz', $this->getConfig('foo.bar'));
    }

    public function testDotNotationCanBeUsed()
    {
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        foo.bar.baz: qux
        YAML);

        $this->runBootstrappers();

        $this->assertSame(['foo' => ['bar' => ['baz' => 'qux']]], $this->getConfig('hyde'));
        $this->assertSame('qux', $this->getConfig('hyde.foo.bar.baz'));
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

        $this->assertSame($expected, $this->getConfig('hyde'));
        $this->assertSame($expected, $this->getConfig('one'));
        $this->assertSame($expected, $this->getConfig('two'));
    }

    public function testSettingSiteNameSetsEnvironmentVariable()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->assertSame('Example', $this->hydeExec('echo config(\'hyde.name\');'));
    }

    public function testSettingSiteNameSetsEnvironmentVariableCanBeTestedReliably()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Another
        YAML);

        $this->assertSame('Another', $this->hydeExec('echo config(\'hyde.name\');'));
    }

    public function testSettingSiteNameSetsSidebarHeader()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example Docs', $this->getConfig('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderWhenUsingHydeNamespace()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example Docs', $this->getConfig('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderUnlessAlreadySpecifiedInYamlConfig()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        docs:
            sidebar:
                header: Custom
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Custom', $this->getConfig('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderUnlessAlreadySpecifiedInStandardConfig()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        config(['docs.sidebar.header' => 'Custom']);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Custom', $this->getConfig('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsRssFeedSiteName()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example RSS Feed', $this->getConfig('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameWhenUsingHydeNamespace()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example RSS Feed', $this->getConfig('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameUnlessAlreadySpecifiedInYamlConfig()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
            rss:
                description: Custom
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Custom', $this->getConfig('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameUnlessAlreadySpecifiedInStandardConfig()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        config(['hyde.rss.description' => 'Custom']);

        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Custom', $this->getConfig('hyde.rss.description'));
    }

    protected function runBootstrappers(): void
    {
        $this->app->bootstrapWith([
            LoadYamlEnvironmentVariables::class,
            LoadYamlConfiguration::class,
            LoadConfiguration::class,
        ]);
    }

    protected function getConfig(string $key): mixed
    {
        return Config::get($key);
    }

    protected function hydeExec(string $code): string
    {
        // Due to how environment data handling is hardcoded in so many places,
        // we can't reliably test these features, as we can't reset the testing
        // environment after each test. We thus need to run the code in a
        // separate process to ensure a clean slate. This means we lose
        // code coverage, but at least we can test the feature.

        $output = shell_exec('php hyde tinker --execute="'.$code.'" exit;');

        $output = str_replace('INFO  Goodbye.', '', $output);

        return trim($output);
    }
}
