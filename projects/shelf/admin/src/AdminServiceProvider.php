<?php

namespace Hyde\Admin;

use Hyde\Hyde;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyde-admin');

        Hyde::routes()->addRoute(
            (new AdminPage('hyde-admin::dashboard'))->getRoute()
        );
    }
}
