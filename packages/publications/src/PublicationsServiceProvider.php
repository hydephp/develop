<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Hyde\Publications\Providers\TranslationServiceProvider;
use Hyde\Publications\Views\Components\RelatedPublicationsComponent;

use function resource_path;

class PublicationsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->app->make(HydeKernel::class)->registerExtension(PublicationsExtension::class);

        $this->commands([
            Commands\MakePublicationTypeCommand::class,
            Commands\MakePublicationCommand::class,

            Commands\ValidatePublicationTypesCommand::class,
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

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/hyde-publications'),
        ], 'hyde-publications-views');

        Blade::component('hyde-publications::related-publications', RelatedPublicationsComponent::class);
    }

    /**
     * Register additional service providers.
     */
    protected function registerAdditionalServiceProviders(): void
    {
        $this->app->register(TranslationServiceProvider::class);
        $this->app->register(ValidationServiceProvider::class);
    }
}
