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

        $hydeColor = "\033[34m";
        $phpColor  = "\033[33m";

        return <<<ASCII
        
        $hydeColor     __ __        __   {$phpColor} ___  __ _____
        $hydeColor    / // /_ _____/ /__ {$phpColor}/ _ \/ // / _ \
        $hydeColor   / _  / // / _  / -_){$phpColor} ___/ _  / ___/
        $hydeColor  /_//_/\_, /\_,_/\__/{$phpColor}_/  /_//_/_/
        $hydeColor       /___/
            
        \033[0m
        ASCII;
    }

    public function boot(): void
    {
        //
    }
}
