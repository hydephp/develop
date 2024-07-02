<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Illuminate\Config\Repository;
use Hyde\Foundation\Internal\LoadYamlConfiguration;
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

        $this->assertSame('HydePHP', config('hyde.name'));
        $this->assertSame('http://localhost', config('hyde.url'));
        $this->assertSame(false, config('hyde.pretty_urls'));
        $this->assertSame(true, config('hyde.generate_sitemap'));
        $this->assertSame(true, config('hyde.rss.enabled'));
        $this->assertSame('feed.xml', config('hyde.rss.filename'));
        $this->assertSame('HydePHP RSS Feed', config('hyde.rss.description'));
        $this->assertSame('en', config('hyde.language'));
        $this->assertSame('_site', config('hyde.output_directory'));
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

        $this->assertSame('HydePHP', config('hyde.name'));
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
        config(['hyde' => [
            'foo' => 'bar',
            'baz' => 'qux',
        ]]);

        $this->file('hyde.yml', 'baz: hat');
        $this->runBootstrappers();

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
        config(['hyde' => []]);

        $this->file('hyde.yml', <<<'YAML'
        foo.bar.baz: qux
        YAML);

        $this->runBootstrappers();

        $this->assertSame(['foo' => ['bar' => ['baz' => 'qux']]], config('hyde'));
        $this->assertSame('qux', config('hyde.foo.bar.baz'));
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

        $this->assertSame($expected, config('hyde'));
        $this->assertSame($expected, config('one'));
        $this->assertSame($expected, config('two'));
    }

    public function testSettingSiteNameSetsAffectsEnvironmentVariableUsages()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->assertSame('Example', $this->getExecConfig()->get('hyde.name'));
    }

    public function testSettingSiteNameSetsEnvironmentVariableCanBeTestedReliably()
    {
        $this->file('hyde.yml', <<<'YAML'
        name: Another
        YAML);

        $this->assertSame('Another', $this->getExecConfig()->get('hyde.name'));
    }

    public function testSettingSiteNameSetsSidebarHeader()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example Docs', config('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsSidebarHeaderWhenUsingHydeNamespace()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example Docs', config('docs.sidebar.header'));
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

        $this->assertSame('Custom', config('docs.sidebar.header'));
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

        $this->assertSame('Custom', config('docs.sidebar.header'));
    }

    public function testSettingSiteNameSetsRssFeedSiteName()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example RSS Feed', config('hyde.rss.description'));
    }

    public function testSettingSiteNameSetsRssFeedSiteNameWhenUsingHydeNamespace()
    {
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/1773#issuecomment-2200933291');
        $this->file('hyde.yml', <<<'YAML'
        hyde:
            name: Example
        YAML);

        $this->runBootstrappers();

        $this->assertSame('Example RSS Feed', config('hyde.rss.description'));
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

        $this->assertSame('Custom', config('hyde.rss.description'));
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

        $this->assertSame('Custom', config('hyde.rss.description'));
    }

    protected function runBootstrappers(): void
    {
        $this->app->bootstrapWith([
            LoadYamlEnvironmentVariables::class,
            LoadYamlConfiguration::class,
        ]);
    }

    protected function getExecConfig(): Repository
    {
        // Due to how environment data handling is hardcoded in so many places,
        // we can't reliably test these features, as we can't reset the testing
        // environment after each test. We thus need to run the code in a
        // separate process to ensure a clean slate. This means we lose
        // code coverage, but at least we can test the feature.

        $code = 'var_export(config()->all())';
        $output = shell_exec('php hyde tinker --execute="'.$code.'" exit;');

        // On the following, __set_state does not exist so we turn it into an array
        $output = str_replace('Hyde\Framework\Features\Metadata\Elements\MetadataElement::__set_state', 'collect', $output);
        $output = str_replace('Hyde\Framework\Features\Metadata\Elements\OpenGraphElement::__set_state', 'collect', $output);
        $output = str_replace('Hyde\Framework\Features\Blogging\Models\PostAuthor::__set_state', 'collect', $output);

        $config = eval('return '.$output.';');

        return new Repository($config);
    }
}
