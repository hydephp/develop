<?php

namespace Hyde\Framework\Modules\Router\Concerns;

interface RoutableContract
{
    public static function getRouteSourcePath(): string;
    public static function getRouteOutputPath(): string;

    /** @see \Hyde\Framework\Contracts\PageContract::files() */
    public static function files(): array;
}
