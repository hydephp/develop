<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Illuminate\Support\ServiceProvider;
use Hyde\Framework\Features\Blogging\BlogPostAuthorPages;

/**
 * @experimental General feature service provider for Hyde.
 */
class FeatureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BlogPostAuthorPages::class);
    }

    public function boot(): void
    {
        //
    }
}
