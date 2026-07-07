<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;
use Illuminate\Support\HtmlString;

class HybridPageCompiler
{
    /**
     * Contains the blocks keyed by their hash code.
     *
     * @var array<string, \Hyde\Pages\HybridPages\HybridPageBlock
     */
    protected array $blocks = [];

    public function handle(HybridPage $page): HtmlString
    {
        return $page->markdown->toHtml($page::class);
    }
}
