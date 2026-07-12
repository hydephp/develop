<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing\BladeBlocks;

use Illuminate\Support\Facades\Blade;

class BladeRenderBlock extends BladeBlock
{
    protected function render(): string
    {
        return Blade::render($this->content);
    }
}
