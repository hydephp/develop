<?php

namespace Hyde\Admin;

use Illuminate\Support\ServiceProvider;
use Hyde\Framework\Services\RoutingService;

class AdminServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'hyde-admin');

        RoutingService::getInstance()->addRoute(
            (new AdminPage('hyde-admin::dashboard'))->getRoute()
        );
    }
}
