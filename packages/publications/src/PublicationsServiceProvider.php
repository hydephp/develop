<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Illuminate\Support\ServiceProvider;

class PublicationsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->commands([
            Commands\MakePublicationTagCommand::class,
            Commands\MakePublicationTypeCommand::class,
            Commands\MakePublicationCommand::class,

            Commands\ValidatePublicationsCommand::class,
            Commands\SeedPublicationCommand::class,
        ]);
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyde-publications');
    }
}
