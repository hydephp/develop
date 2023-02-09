<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Framework\Services\YamlConfigurationService;
use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
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
        // Hyde Configuration Files
        $this->mergeConfigFrom(__DIR__.'/../../../config/hyde.php', 'hyde');
        $this->mergeConfigFrom(__DIR__.'/../../../config/docs.php', 'docs');
        $this->mergeConfigFrom(__DIR__.'/../../../config/site.php', 'site');
        $this->mergeConfigFrom(__DIR__.'/../../../config/markdown.php', 'markdown');

        // Illuminate/Vendor Configuration Files
        $this->mergeConfigFrom(__DIR__.'/../../../config/view.php', 'view');
        $this->mergeConfigFrom(__DIR__.'/../../../config/cache.php', 'cache');
        $this->mergeConfigFrom(__DIR__.'/../../../config/torchlight.php', 'torchlight');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../config' => config_path(),
        ], 'configs');
    }
}
