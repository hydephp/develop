<?php

declare(strict_types=1);

namespace Hyde\Framework\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }

    public function provides(): array
    {
        return [];
    }
}
