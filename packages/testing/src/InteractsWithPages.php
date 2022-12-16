<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Facades\Render;
use Hyde\Support\Models\Route;

trait InteractsWithPages
{
    protected function mockRoute(?Route $route = null)
    {
        Render::share('currentRoute', $route ?? (new Route(new MarkdownPage())));
    }

    protected function mockPage(?HydePage $page = null, ?string $currentPage = null)
    {
        Render::share('page', $page ?? new MarkdownPage());
        Render::share('currentPage', $currentPage ?? 'PHPUnit');
    }

    protected function mockCurrentPage(string $currentPage)
    {
        Render::share('currentPage', $currentPage);
    }
}
