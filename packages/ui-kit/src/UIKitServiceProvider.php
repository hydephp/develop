<?php

declare(strict_types=1);

namespace Hyde\UIKit;

use Illuminate\Support\ServiceProvider;

class UIKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyde-ui');
    }
}
