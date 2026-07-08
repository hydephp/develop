<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Illuminate\Support\Facades\Blade;

class BladePageBlock extends HybridPageBlock
{
    protected function render(): string
    {
        return Blade::render($this->content);
    }
}
