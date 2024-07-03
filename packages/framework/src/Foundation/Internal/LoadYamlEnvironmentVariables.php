<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Illuminate\Support\Env;
use Hyde\Foundation\Application;

use function filled;

/**
 * @internal Inject environment variables parsed from the YAML configuration file.
 */
class LoadYamlEnvironmentVariables
{
    protected YamlConfigurationRepository $yaml;

    public function bootstrap(Application $app): void
    {
        $this->yaml = $app->make(YamlConfigurationRepository::class);

        if ($this->yaml->hasYamlConfigFile()) {
            $this->injectEnvironmentVariables();
        }
    }

    protected function injectEnvironmentVariables(): void
    {
        if ($this->canInjectSiteNameEnvironmentVariable()) {
            $this->injectSiteNameEnvironmentVariable();
        }
    }

    protected function canInjectSiteNameEnvironmentVariable(): bool
    {
        return $this->yamlHasSiteNameSet() && ! $this->alreadyHasEnvironmentVariable();
    }

    protected function alreadyHasEnvironmentVariable(): bool
    {
        return filled(Env::get('SITE_NAME'));
    }

    protected function injectSiteNameEnvironmentVariable(): void
    {
        $name = $this->getSiteNameFromYaml();

        Env::getRepository()->set('SITE_NAME', $name);
    }

    protected function yamlHasSiteNameSet(): bool
    {
        return isset($this->yaml->getData()['hyde']['name']);
    }

    protected function getSiteNameFromYaml(): string
    {
        return $this->yaml->getData()['hyde']['name'];
    }
}
