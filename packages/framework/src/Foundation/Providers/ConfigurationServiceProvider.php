<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
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
