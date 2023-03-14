<?php

declare(strict_types=1);

namespace Hyde\Foundation\Providers;

use Hyde\Hyde;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Hyde\Framework\Views\Components\LinkComponent;
use Hyde\Framework\Views\Components\BreadcrumbsComponent;

use function resource_path;

/**
 * Register the Hyde view components.
 */
class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../../resources/views', 'hyde');

        $this->publishes([
            __DIR__.'/../../../resources/views/layouts' => resource_path('views/vendor/hyde/layouts'),
        ], 'hyde-layouts');

        $this->publishes([
            __DIR__.'/../../../resources/views/components' => resource_path('views/vendor/hyde/components'),
        ], 'hyde-components');

        $this->publishes([
            Hyde::vendorPath('resources/views/pages/404.blade.php') => Hyde::path('_pages/404.blade.php'),
        ], 'hyde-page-404');

        $this->publishes([
            Hyde::vendorPath('resources/views/homepages/welcome.blade.php') => Hyde::path('_pages/index.blade.php'),
        ], 'hyde-welcome-page');

        $this->publishes([
            Hyde::vendorPath('resources/views/homepages/post-feed.blade.php') => Hyde::path('_pages/index.blade.php'),
        ], 'hyde-posts-page');

        $this->publishes([
            Hyde::vendorPath('resources/views/homepages/blank.blade.php') => Hyde::path('_pages/index.blade.php'),
        ], 'hyde-blank-page');

        Blade::component('link', LinkComponent::class);
        Blade::component('hyde::breadcrumbs', BreadcrumbsComponent::class);
    }
}
