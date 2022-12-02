<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\View;

/**
 * Contains data for the current page being rendered/compiled.
 *
 * All public data here will be available in the Blade views through @see ManagesViewData::shareViewData().
 *
 * @see \Hyde\Support\Facades\Render
 * @see \Hyde\Framework\Testing\Feature\RenderHelperTest
 *
 * @todo Refactor into singleton and add facade
 * @todo Refactor to actually utilize this class
 */
class Render
{
    protected static HydePage $page;
    protected static Route $currentRoute;
    protected static string $currentPage;

    protected static array $data = [];

    public static function setPage(HydePage $page): void
    {
        static::$page = $page;
        static::$currentRoute = $page->getRoute();
        static::$currentPage = $page->getRouteKey();
    }

    public static function getPage(): ?HydePage
    {
        return static::$page ?? null;
    }

    public static function getCurrentRoute(): ?Route
    {
        return static::$currentRoute ?? null;
    }

    public static function getCurrentPage(): ?string
    {
        return static::$currentPage ?? null;
    }

    public static function share(string $key, mixed $value): void
    {
        static::$data[$key] = $value;
    }

    public static function shared(string $key, mixed $default = null): mixed
    {
        return static::$data[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset(static::$data[$key]);
    }

    public static function shareToView(): void
    {
        View::share('page', static::getPage());
        View::share('currentRoute', static::getCurrentRoute());
        View::share('currentPage', static::getCurrentPage());
    }
}
