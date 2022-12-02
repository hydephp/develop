<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Manages data for the current page being rendered/compiled.
 *
 * @see \Hyde\Support\Models\Render
 */
class Render extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hyde\Support\Models\Render::class;
    }
}
