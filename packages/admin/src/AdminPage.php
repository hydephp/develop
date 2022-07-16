<?php

namespace Hyde\Admin;

use Hyde\Framework\Contracts\PageContract;
use Hyde\Framework\Models\Pages\BladePage;

final class AdminPage extends BladePage implements PageContract
{
    public function getCurrentPagePath(): string
    {
        return 'admin';
    }
}
