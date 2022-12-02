<?php

declare(strict_types=1);

namespace Hyde\Support\Facades;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;
use Illuminate\Support\Facades\Facade;

/**
 * Manages data for the current page being rendered/compiled.
 *
 * @see \Hyde\Support\Models\Render
 *
 * @method static void setPage(HydePage $page)
 * @method static HydePage getPage()
 * @method static Route getCurrentRoute()
 * @method static string getCurrentPage()
 * @method static void share(string $key, mixed $value)
 * @method static mixed shared(string $key, mixed $default = null)
 * @method static bool has(string $key)
 */
class Render extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hyde\Support\Models\Render::class;
    }
}
