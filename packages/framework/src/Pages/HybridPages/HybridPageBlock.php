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
        return sprintf('<!-- HYDE[HybridPageBlock]%s -->', $this->hash());
    }

    protected function hash(): string
    {
        return hash('sha256', static::class."\0".$this->content);
    }

    abstract public function render();
}
