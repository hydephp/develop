<?php

declare(strict_types=1);

namespace Hyde\Console;

use Illuminate\Support\ServiceProvider;

use Illuminate\Console\Application as Artisan;

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
        $this->commands([
            Commands\BuildRssFeedCommand::class,
            Commands\BuildSearchCommand::class,
            Commands\BuildSiteCommand::class,
            Commands\BuildSitemapCommand::class,
            Commands\RebuildStaticSiteCommand::class,

            Commands\MakePageCommand::class,
            Commands\MakePostCommand::class,

            Commands\PublishHomepageCommand::class,
            Commands\PublishViewsCommand::class,
            Commands\UpdateConfigsCommand::class,
            Commands\PackageDiscoverCommand::class,

            Commands\RouteListCommand::class,
            Commands\ValidateCommand::class,
            Commands\ServeCommand::class,
            Commands\DebugCommand::class,
        ]);

        Artisan::starting(function (Artisan $artisan): void {
            $artisan->setName(self::logo());
        });
    }

    protected static function logo(): string
    {
        return <<<ASCII
        
        \033[34m     __ __        __   \033[33m ___  __ _____
        \033[34m    / // /_ _____/ /__ \033[33m/ _ \/ // / _ \
        \033[34m   / _  / // / _  / -_)\033[33m ___/ _  / ___/
        \033[34m  /_//_/\_, /\_,_/\__/\033[33m_/  /_//_/_/
        \033[34m       /___/
            
        \033[0m
        ASCII;
    }
}
