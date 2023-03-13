<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Illuminate\Support\ServiceProvider;

use function config_path;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../../config/hyde.php', 'hyde');
        $this->mergeConfigFrom(__DIR__.'/../../../config/docs.php', 'docs');
        $this->mergeConfigFrom(__DIR__.'/../../../config/markdown.php', 'markdown');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../config' => config_path(),
        ], 'configs');

        $this->publishes([
            __DIR__.'/../../../config/hyde.php' => config_path('hyde.php'),
            __DIR__.'/../../../config/docs.php' => config_path('docs.php'),
            __DIR__.'/../../../config/markdown.php' => config_path('markdown.php'),
        ], 'hyde-configs');

        $this->publishes([
            __DIR__.'/../../../config/view.php' => config_path('view.php'),
            __DIR__.'/../../../config/cache.php' => config_path('cache.php'),
            __DIR__.'/../../../config/commands.php' => config_path('commands.php'),
            __DIR__.'/../../../config/torchlight.php' => config_path('torchlight.php'),
        ], 'support-configs');
    }
}
