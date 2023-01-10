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

        $this->registerAdditionalServiceProviders();
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyde-publications');
    }

    /**
     * Register additional service providers.
     */
    protected function registerAdditionalServiceProviders(): void
    {
        $this->app->register(\Hyde\Publications\Providers\TranslationServiceProvider::class);
        $this->app->register(\Illuminate\Validation\ValidationServiceProvider::class);
    }
}
