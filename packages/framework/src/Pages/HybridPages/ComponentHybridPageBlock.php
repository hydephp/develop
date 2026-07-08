<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;

class ComponentHybridPageBlock extends HybridPageBlock
{
    protected string $name;

    public function __construct(HybridPage $page, string $content, string $name)
    {
        parent::__construct($page, $content);

        $this->name = $name;
    }

    public function render()
    {
        // TODO: Implement render() method.
    }

    protected function hash(): string
    {
        return hash('sha256', static::class."\0".$this->name."\0".$this->content);
    }
}
