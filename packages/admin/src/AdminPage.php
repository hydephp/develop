<?php

namespace Hyde\Admin;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;

final class AdminPage extends BladePage implements PageContract
{
    /** Not yet implemented, but could be used by the framework to ignore this when compiling */
    protected static bool $renderable = false;

    public function getCurrentPagePath(): string
    {
        return 'admin';
    }
}
