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
 * @method static HydePage|null getPage()
 * @method static Route|null getCurrentRoute()
 * @method static string|null getCurrentPage()
 * @method static void share(string $key, mixed $value)
 * @method static void shareToView()
 * @method static void clearData()
 */
class Render extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Hyde\Support\Models\Render::class;
    }
}
