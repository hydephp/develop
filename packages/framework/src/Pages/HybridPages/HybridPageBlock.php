<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;

abstract class HybridPageBlock
{
    protected HybridPage $page;
    protected string $content;

    protected readonly string $hash;

    public function __construct(HybridPage $page, string $content)
    {
        $this->page = $page;
        $this->content = $content;

        $this->hash = $this->computeHash();
    }

    public function signature(): string
    {
        return sprintf('<!-- HYDE[HybridPageBlock]%s -->', $this->hash);
    }

    abstract public function render(): string;

    protected function computeHash(): string
    {
        return hash('sha256', static::class . "\0" . $this->content); 
    }
}
