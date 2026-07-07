<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;

abstract class HybridPageBlock
{
    protected HybridPage $page;

    public function __construct(HybridPage $page)
    {
        $this->page = $page;
    }

    public function signature(): string
    {
        $hash = sha1('TODO');

        return "<!-- HYDE[HybridPageBlock]$hash -->";
    }

    abstract public function render();
}
