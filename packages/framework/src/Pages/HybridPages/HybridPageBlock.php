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

    abstract public function render();
}
