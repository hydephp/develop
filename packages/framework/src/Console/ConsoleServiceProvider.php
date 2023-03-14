<?php

declare(strict_types=1);

namespace Hyde\Console;

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\ServiceProvider;

/**
 * Register the HydeCLI console commands.
 */
class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            Commands\BuildRssFeedCommand::class,
            Commands\BuildSearchCommand::class,
            Commands\BuildSiteCommand::class,
            Commands\BuildSitemapCommand::class,
            Commands\RebuildPageCommand::class,

            Commands\MakePageCommand::class,
            Commands\MakePostCommand::class,

            Commands\VendorPublishCommand::class,
            Commands\PublishConfigsCommand::class,
            Commands\PublishHomepageCommand::class,
            Commands\PublishViewsCommand::class,
            Commands\PackageDiscoverCommand::class,

            Commands\RouteListCommand::class,
            Commands\ValidateCommand::class,
            Commands\ServeCommand::class,
            Commands\DebugCommand::class,

            Commands\ChangeSourceDirectoryCommand::class,
        ]);

        Artisan::starting(function (Artisan $artisan): void {
            $artisan->setName(self::logo());
        });
    }

    protected static function logo(): string
    {
        // Check if no-ansi flag is set
        if (isset($_SERVER['argv']) && in_array('--no-ansi', $_SERVER['argv'], true)) {
            return 'HydePHP';
        }

        return <<<ASCII
        
        \033[34m     __ __        __   \033[33m ___  __ _____
        \033[34m    / // /_ _____/ /__ \033[33m/ _ \/ // / _ \
        \033[34m   / _  / // / _  / -_)\033[33m ___/ _  / ___/
        \033[34m  /_//_/\_, /\_,_/\__/\033[33m_/  /_//_/_/
        \033[34m       /___/
            
        \033[0m
        ASCII;
    }

    public function boot(): void
    {
        //
    }
}
