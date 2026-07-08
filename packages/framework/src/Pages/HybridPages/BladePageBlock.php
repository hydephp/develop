<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Illuminate\Support\Facades\Blade;

class BladePageBlock extends HybridPageBlock
{
    public function render(): string
    {
        return Blade::render($this->content);
    }
}
