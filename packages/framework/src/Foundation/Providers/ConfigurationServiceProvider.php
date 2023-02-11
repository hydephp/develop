<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Framework\Services\YamlConfigurationService;
use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * @deprecated Move to bootstrapper so this class can be registered in app.php so users can override it.
     * Run any logic before the Hyde Service Provider is registered.
     */
    public function initialize(): void
    {
        if (YamlConfigurationService::hasFile()) {
            YamlConfigurationService::boot();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../../config/hyde.php', 'hyde');
        $this->mergeConfigFrom(__DIR__.'/../../../config/docs.php', 'docs');
        $this->mergeConfigFrom(__DIR__.'/../../../config/site.php', 'site');
        $this->mergeConfigFrom(__DIR__.'/../../../config/markdown.php', 'markdown');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../config' => config_path(),
        ], 'configs');
    }
}
