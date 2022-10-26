<?php

declare(strict_types=1);

namespace Hyde\Console;

use Illuminate\Support\ServiceProvider;

/**
 * Register the HydeCLI console commands.
 */
class HydeConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register any console services.
     */
    public function register(): void
    {
        $this->registerHydeConsoleCommands();
    }

    /**
     * Register the HydeCLI console commands.
     */
    protected function registerHydeConsoleCommands(): void
    {
        $this->commands([
            Commands\PublishHomepageCommand::class,
            Commands\UpdateConfigsCommand::class,
            Commands\PublishViewsCommand::class,
            Commands\RebuildStaticSiteCommand::class,
            Commands\BuildSiteCommand::class,
            Commands\BuildSitemapCommand::class,
            Commands\BuildRssFeedCommand::class,
            Commands\BuildSearchCommand::class,
            Commands\RouteListCommand::class,
            Commands\MakePostCommand::class,
            Commands\MakePageCommand::class,
            Commands\ValidateCommand::class,
            Commands\DebugCommand::class,
            Commands\ServeCommand::class,

            Commands\PackageDiscoverCommand::class,
        ]);
    }

}
