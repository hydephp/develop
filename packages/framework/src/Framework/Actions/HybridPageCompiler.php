<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Pages\HybridPage;
use Illuminate\Support\HtmlString;

class HybridPageCompiler
{
    public function handle(HybridPage $page): HtmlString
    {
        return $page->markdown->toHtml($page::class);
    }
}
