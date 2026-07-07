<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\HybridPage;

class HybridPageCompiler
{
    /**
     * Contains the blocks keyed by their hash code.
     *
     * @var array<string, \Hyde\Pages\HybridPages\HybridPageBlock
     */
    protected array $blocks = [];

    public function handle(HybridPage $page): string
    {
        return Markdown::render($page->markdown, $page::class);
    }
}
