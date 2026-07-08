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

    protected readonly string $hash;
    public readonly string $signature;

    abstract protected function render(): string;

    public function __construct(HybridPage $page, string $content)
    {
        $this->page = $page;
        $this->content = $content;

        $this->hash = hash('sha256', implode("\0", $this->getHashableContent()));
        $this->signature = sprintf('<!-- HYDE[HybridPageBlock]%s -->', $this->hash);
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
