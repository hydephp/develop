<?php

declare(strict_types=1);

namespace Hyde\Support\Models;

use Hyde\Pages\Concerns\HydePage;

/**
 * Contains data for the current page being rendered/compiled.
 *
 * All public data here will be available in the Blade views through @see ManagesViewData::shareViewData().
 *
 * @see \Hyde\Framework\Testing\Feature\RenderHelperTest
 */
class Render
{
    protected static HydePage $page;
    protected static Route $currentRoute;
    protected static string $currentPage;
}
