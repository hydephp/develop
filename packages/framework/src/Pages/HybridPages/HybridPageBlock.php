<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;

abstract class HybridPageBlock
{
    protected HybridPage $page;
    protected string $content;

    public function __construct(HybridPage $page, string $content)
    {
        $this->page = $page;
        $this->content = $content;
    }

    public function signature(): string
    {
        $hash = hash('sha256', 'TODO');

        return "<!-- HYDE[HybridPageBlock]$hash -->";
    }

    abstract public function render();
}
