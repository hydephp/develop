<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Illuminate\Support\Arr;
use Hyde\Foundation\Application;

use function array_key_first;
use function array_merge;

/**
 * @internal Bootstrap service that loads the YAML configuration file.
 *
 * @see docs/digging-deeper/customization.md#yaml-configuration
 *
 * It also supports loading multiple configuration namespaces, where a configuration namespace is defined
 * as a firs level entry in the service container configuration repository array, and corresponds
 * one-to-one with a file in the config directory, and a root-level key in the YAML file.
 *
 * This feature, by design, requires a top-level configuration entry to be present as 'hyde' in the YAML file.
 * Existing config files will be parsed as normal, but can be migrated by indenting all entries by one level,
 * and adding a top-level 'hyde' key. Then additional namespaces can be added underneath as needed.
 */
class LoadYamlConfiguration
{
    protected array $config;
    protected array $yaml;

    /**
     * Performs a core task that needs to be performed on
     * early stages of the framework.
     */
    public function bootstrap(Application $app): void
    {
        $yaml = $app->make(YamlConfigurationRepository::class);

        if ($yaml->hasYamlConfigFile()) {
            $this->config = $app->make('config')->all();
            $this->yaml = $yaml->getData();

            $this->supportSettingSidebarHeaderFromSiteName();
            $this->supportSettingRssFeedTitleFromSiteName();

            $this->mergeParsedConfiguration();

            $app->make('config')->set($this->config);
        }
    }

    protected function mergeParsedConfiguration(): void
    {
        // If the Yaml file contains namespaces, we merge those using more granular logic
        // that only applies the namespace data to each configuration namespace.
        if ($this->configurationContainsNamespaces()) {
            /** @var array<string, array<string, scalar>> $yaml */
            foreach ($this->yaml as $namespace => $data) {
                $this->mergeConfiguration($namespace, Arr::undot((array) $data));
            }
        } else {
            // Otherwise, we can merge using the default strategy, which is simply applying all the data to the hyde namespace.
            $this->mergeConfiguration('hyde', $this->yaml);
        }
    }

    protected function mergeConfiguration(string $namespace, array $yamlData): void
    {
        $this->config[$namespace] = array_merge(
            $this->config[$namespace] ?? [],
            $yamlData
        );
    }

    protected function configurationContainsNamespaces(): bool
    {
        return array_key_first($this->yaml) === 'hyde';
    }

    /** @deprecated Should not be needed when using dynamic environment variables. */
    private function supportSettingSidebarHeaderFromSiteName(): void
    {
        $sidebarHeaderIsNotSetInPhpConfig = ($this->config['docs']['sidebar']['header'] ?? null) === 'HydePHP Docs';
        $siteNameFromYaml = $this->configurationContainsNamespaces() ? ($this->yaml['hyde']['name'] ?? null) : ($this->yaml['name'] ?? null);

        if ($sidebarHeaderIsNotSetInPhpConfig) {
            if ($siteNameFromYaml !== null) {
                $this->config['docs']['sidebar']['header'] = $siteNameFromYaml.' Docs';
            }
        }
    }

    /** @deprecated Should not be needed when using dynamic environment variables. */
    private function supportSettingRssFeedTitleFromSiteName(): void
    {
        $rssFeedTitleIsNotSetInPhpConfig = ($this->config['hyde']['rss']['description'] ?? null) === 'HydePHP RSS Feed';
        $siteNameFromYaml = $this->configurationContainsNamespaces() ? ($this->yaml['hyde']['name'] ?? null) : ($this->yaml['name'] ?? null);

        if ($rssFeedTitleIsNotSetInPhpConfig) {
            if ($siteNameFromYaml !== null) {
                $this->config['hyde']['rss']['description'] = $siteNameFromYaml.' RSS Feed';
            }
        }
    }
}
