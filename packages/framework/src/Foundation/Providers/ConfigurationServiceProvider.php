<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Framework\Services\YamlConfigurationService;
use Illuminate\Support\ServiceProvider;

class ConfigurationServiceProvider extends ServiceProvider
{
    public function initialize(): void
    {
        if (YamlConfigurationService::hasFile()) {
            YamlConfigurationService::boot();
        }
    }

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
