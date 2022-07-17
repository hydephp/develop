<?php

namespace Hyde\Admin;

use Hyde\Framework\Services\RoutingService;
use Illuminate\Support\ServiceProvider;

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
