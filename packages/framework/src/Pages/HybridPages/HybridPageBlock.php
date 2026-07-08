<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;

use function hash;
use function implode;
use function sprintf;

abstract class HybridPageBlock
{
    protected HybridPage $page;
    protected string $content;

    public readonly string $signature;

    abstract protected function render(): string;

    public function __construct(HybridPage $page, string $content)
    {
        $this->page = $page;
        $this->content = $content;

        $this->signature = sprintf('<!-- HYDE[HybridPageBlock]%s -->',
            hash('sha256', implode("\0", $this->getHashableContent())),
        );
    }

    public function compile(): string
    {
        return sprintf(
            '<div class="hybrid-container not-prose">%s</div>',
            $this->render(),
        );
    }

    /** @return array<string> */
    protected function getHashableContent(): array
    {
        return [static::class, $this->content];
    }
}
