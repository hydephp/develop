<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

class BladePageBlock extends HybridPageBlock
{
    public function render(): string
    {
        return $this->content;
    }
}
