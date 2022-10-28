<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Session;

use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Session::class, Session::class);
    }

    public function boot()
    {
        //
    }
}
