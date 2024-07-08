<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Illuminate\Support\Env;

/**
 * Test the Yaml configuration feature.
 *
 * @see \Hyde\Framework\Testing\Feature\HighLevelYamlConfigurationFeatureTest
 *
 * @covers \Hyde\Foundation\Internal\LoadYamlConfiguration
 * @covers \Hyde\Foundation\Internal\LoadYamlEnvironmentVariables
 * @covers \Hyde\Foundation\Internal\YamlConfigurationRepository
 */
class YamlConfigurationFeatureTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->clearEnvVars();

        parent::tearDown();
    }

    public function testCanDefineHydeConfigSettingsInHydeYmlFile()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Test
        url: "http://localhost"
        pretty_urls: false
        generate_sitemap: true
        rss:
          enabled: true
          filename: feed.xml
          description: Test RSS Feed
        language: en
        output_directory: _site
        YAML);
        $this->runBootstrappers();

        $this->assertSame('Test', config('hyde.name'));
        $this->assertSame('http://localhost', config('hyde.url'));
        $this->assertSame('feed.xml', config('hyde.rss.filename'));
        $this->assertSame('Test RSS Feed', config('hyde.rss.description'));
        $this->assertSame('en', config('hyde.language'));
        $this->assertSame('_site', config('hyde.output_directory'));
        $this->assertTrue(config('hyde.generate_sitemap'));
        $this->assertTrue(config('hyde.rss.enabled'));
        $this->assertFalse(config('hyde.pretty_urls'));
    }

    public function testCanDefineMultipleConfigSettingsInHydeYmlFile()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Test
            url: "http://localhost"
        docs:
            sidebar:
                header: "My Docs"
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Test', config('hyde.name'));
        $this->assertSame('http://localhost', config('hyde.url'));
        $this->assertSame('My Docs', config('docs.sidebar.header'));
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

        $this->assertSame('Foo', config('hyde.name'));
    }

    public function testChangesInYamlFileOverrideChangesInHydeConfigWhenUsingYamlExtension()
    {
        $this->file('hyde.yaml', 'name: Foo');
        $this->runBootstrappers();

        $this->assertSame('Foo', config('hyde.name'));
    }

    public function testServiceGracefullyHandlesMissingFile()
    {
        $this->runBootstrappers();

        $this->assertSame('HydePHP', config('hyde.name'));
    }

    public function testServiceGracefullyHandlesEmptyFile()
    {
        $this->file('hyde.yml', '');
        $this->runBootstrappers();

        $this->assertSame('HydePHP', config('hyde.name'));
    }

    public function testCanAddArbitraryConfigKeys()
    {
        $this->file('hyde.yml', 'foo: bar');
        $this->runBootstrappers();

        $this->assertSame('bar', config('hyde.foo'));
    }

    public function testConfigurationOptionsAreMerged()
    {
        $this->file('hyde.yml', 'baz: hat');
        $this->runBootstrappers(['hyde' => [
            'foo' => 'bar',
            'baz' => 'qux',
        ]]);

        $this->assertSame('bar', config('hyde.foo'));
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

        $this->assertSame('HydePHP', config('hyde.name'));
        $this->assertSame('bar', config('hyde.foo'));
        $this->assertSame('qux', config('hyde.bar.baz'));
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

        $this->assertSame('baz', config('foo.bar'));
    }

    public function testAdditionalNamespacesRequireTheHydeNamespaceToBePresent()
    {
        $this->file('hyde.yml', <<<'YAML'
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

        $this->assertNull(config('foo.bar'));
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

        $this->assertNull(config('foo.bar'));
    }

    public function testHydeNamespaceCanBeEmpty()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

        $this->assertSame('baz', config('foo.bar'));
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

        $this->assertSame('baz', config('foo.bar'));
    }

    public function testHydeNamespaceCanBlank()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde: ''
        foo:
          bar: baz
        YAML);

        $this->runBootstrappers();

        $this->assertSame('baz', config('foo.bar'));
    }

    public function testDotNotationCanBeUsed()
    {
        $this->file('hyde.yml', <<<'YAML'
        foo.bar.baz: qux
        YAML);

        $this->runBootstrappers();

        $this->assertSame(['bar' => ['baz' => 'qux']], config('hyde.foo'));
        $this->assertSame('qux', config('hyde.foo.bar.baz'));
    }

    public function testDotNotationCanBeUsedWithNamespaces()
    {
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

        $expected = ['bar' => ['baz' => 'qux']];

        $this->assertSame($expected, config('hyde.foo'));
        $this->assertSame($expected, config('one.foo'));
        $this->assertSame($expected, config('two.foo'));
    }

    public function testSettingSiteNameSetsEnvVars()
    {
        $this->assertSame('HydePHP', config('hyde.name'));

        // Assert that the environment variables are not set.
        $this->assertSame([
            'env' => null,
            'Env::get' => null,
            'getenv' => false,
            '$_ENV' => null,
            '$_SERVER' => null,
        ], $this->envVars());

        $this->file('hyde.yml', <<<'YAML'
        name: Environment Example
        YAML);

        $this->runBootstrappers();

        // Assert that the environment variables are set.
        $this->assertSame([
            'env' => 'Environment Example',
            'Env::get' => 'Environment Example',
            'getenv' => 'Environment Example',
            '$_ENV' => 'Environment Example',
            '$_SERVER' => 'Environment Example',
        ], $this->envVars());

        $this->assertSame('Environment Example', config('hyde.name'));
    }

    public function testSettingSiteNameSetsSidebarHeader()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Root Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Root Example Docs', config('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderWhenUsingHydeNamespace()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Hyde Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Hyde Example Docs', config('docs.sidebar.header'));
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

        $this->assertSame('Custom', config('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderUnlessAlreadySpecifiedInStandardConfig()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers(['docs.sidebar.header' => 'Custom']);

        $this->assertSame('Custom', config('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsRssFeedSiteName()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example RSS Feed', config('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameWhenUsingHydeNamespace()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example RSS Feed', config('hyde.rss.description'));
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

        $this->assertSame('Custom', config('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameUnlessAlreadySpecifiedInStandardConfig()
    {
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers(['hyde.rss.description' => 'Custom']);

        $this->assertSame('Custom', config('hyde.rss.description'));
    }

    protected function runBootstrappers(?array $withMergedConfig = null): void
    {
        $this->refreshApplication();

        if ($withMergedConfig !== null) {
            $this->app['config']->set($withMergedConfig);
        }
    }

    protected function clearEnvVars(): void
    {
        // Todo: Can we access loader? https://github.com/vlucas/phpdotenv/pull/107/files
        putenv('SITE_NAME');
        unset($_ENV['SITE_NAME'], $_SERVER['SITE_NAME']);
    }

    protected function envVars(): array
    {
        return [
            'env' => env('SITE_NAME'),
            'Env::get' => Env::get('SITE_NAME'),
            'getenv' => getenv('SITE_NAME'),
            '$_ENV' => $_ENV['SITE_NAME'] ?? null,
            '$_SERVER' => $_SERVER['SITE_NAME'] ?? null,
        ];
    }
}
