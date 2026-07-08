<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;

class ComponentPageBlock extends HybridPageBlock
{
    protected string $name;

    public function __construct(HybridPage $page, string $content, string $name)
    {
        $this->name = $name;

        parent::__construct($page, $content);
    }

    public function render(): string
    {
        return $this->content;
    }

    protected function hash(): string
    {
        return hash('sha256', static::class."\0".$this->name."\0".$this->content);
    }
}
