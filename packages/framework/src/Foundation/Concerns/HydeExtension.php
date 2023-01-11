<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\FileCollection;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;

abstract class HydeExtension
{
    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public static function getPageClasses(): array
    {
        return [];
    }

    public static function discoverFiles(FileCollection $collection): void
    {
        //
    }

    public static function discoverPages(PageCollection $collection): void
    {
        //
    }

    public static function discoverRoutes(RouteCollection $collection): void
    {
        //
    }
}
