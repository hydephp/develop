<?php

declare(strict_types=1);

namespace Hyde\Console;

use Illuminate\Support\ServiceProvider;

class HydeConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register any console services.
     *
     * @return void
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
            \Hyde\Console\Commands\PublishHomepageCommand::class,
            \Hyde\Console\Commands\UpdateConfigsCommand::class,
            \Hyde\Console\Commands\PublishViewsCommand::class,
            \Hyde\Console\Commands\RebuildStaticSiteCommand::class,
            \Hyde\Console\Commands\BuildSiteCommand::class,
            \Hyde\Console\Commands\BuildSitemapCommand::class,
            \Hyde\Console\Commands\BuildRssFeedCommand::class,
            \Hyde\Console\Commands\BuildSearchCommand::class,
            \Hyde\Console\Commands\RouteListCommand::class,
            \Hyde\Console\Commands\MakePostCommand::class,
            \Hyde\Console\Commands\MakePageCommand::class,
            \Hyde\Console\Commands\ValidateCommand::class,
            \Hyde\Console\Commands\DebugCommand::class,
            \Hyde\Console\Commands\ServeCommand::class,

            \Hyde\Console\Commands\PackageDiscoverCommand::class,
        ]);
    }

}
