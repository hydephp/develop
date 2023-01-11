<?php

declare(strict_types=1);

namespace Hyde\Foundation\Extensions;

abstract class HydeExtension
{
    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public static function getPageClasses(): array
    {
        return [];
    }

    public static function discoverFiles(): void
    {
        //
    }

    public static function discoverPages(): void
    {
        //
    }

    public static function discoverRoutes(): void
    {
        //
    }
}
