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
        $this->commands(
            [
                Commands\BuildRssFeedCommand::class,
                Commands\BuildSearchCommand::class,
                Commands\BuildSiteCommand::class,
                Commands\BuildSitemapCommand::class,
                Commands\RebuildStaticSiteCommand::class,

                Commands\MakePageCommand::class,
                Commands\MakePostCommand::class,
                \Hyde\Publications\Commands\MakePublicationTagCommand::class,
                \Hyde\Publications\Commands\MakePublicationTypeCommand::class,
                \Hyde\Publications\Commands\MakePublicationCommand::class,

                Commands\PublishHomepageCommand::class,
                Commands\PublishViewsCommand::class,
                Commands\UpdateConfigsCommand::class,
                Commands\PackageDiscoverCommand::class,

                Commands\RouteListCommand::class,
                Commands\ValidateCommand::class,
                Commands\ServeCommand::class,
                Commands\DebugCommand::class,

                \Hyde\Publications\Commands\ValidatePublicationsCommand::class,
                \Hyde\Publications\Commands\SeedPublicationCommand::class,
            ]
        );
    }
}
