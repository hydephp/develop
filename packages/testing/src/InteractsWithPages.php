<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Pages\InMemoryPage;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;

trait InteractsWithPages
{
    protected function mockRoute(?Route $route = null): void
    {
        Render::share('route', $route ?? (new Route(new InMemoryPage())));
    }

    protected function mockPage(?HydePage $page = null, ?string $currentPage = null): void
    {
        Render::share('page', $page ?? new InMemoryPage());
        Render::share('routeKey', $currentPage ?? 'PHPUnit');
    }

    protected function mockCurrentPage(string $currentPage): void
    {
        Render::share('routeKey', $currentPage);
        Render::share('route', new Route(new InMemoryPage($currentPage)));
    }
}
