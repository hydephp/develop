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

    public static function setPage(HydePage $page): void
    {
        static::$page = $page;
        static::$currentRoute = $page->getRoute();
        static::$currentPage = $page->getRouteKey();
    }

    public static function getPage(): ?HydePage
    {
        return static::$page ?? self::handleFallback('page');
    }

    public static function getCurrentRoute(): ?Route
    {
        return static::$currentRoute ?? self::handleFallback('currentRoute');
    }

    public static function getCurrentPage(): ?string
    {
        return static::$currentPage ?? self::handleFallback('currentPage');
    }

    public static function shareToView(): void
    {
        View::share('page', static::getPage());
        View::share('currentRoute', static::getCurrentRoute());
        View::share('currentPage', static::getCurrentPage());
    }

    /** @codeCoverageIgnore */
    protected static function handleFallback(string $property): mixed
    {
        $shared = View::shared($property);

        if ($shared !== null) {
            trigger_error("Setting page rendering data via the view facade is deprecated. Use the Render model/facade instead.", E_USER_DEPRECATED);
        }

        return $shared;
    }
}
