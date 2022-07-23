<?php

namespace Hyde\Testing;

use Illuminate\Support\ServiceProvider;

class TestingServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if (env('DUSK_ENABLED', false)) {
            $this->app->register(\Hyde\Testing\DuskServiceProvider::class);
        }
    }
}
