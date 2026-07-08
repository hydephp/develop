<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\HybridPage;

class HybridPageCompiler
{
    /**
     * Contains the blocks keyed by their hash code.
     *
     * @var array<string, \Hyde\Pages\HybridPages\HybridPageBlock>
     */
    protected array $blocks = [];

    protected HybridPage $page;

    public function __construct(HybridPage $page)
    {
        $this->page = $page;
    }

    public function handle(): string
    {
        $markdown = $this->page->markdown;

        [$this->blocks, $markdown] = (new HybridPageBlockExtractor($this->page))
            ->handle($markdown->body());

        $html = Markdown::render($markdown, $this->page::class);

        $html = $this->injectCompiledBlocks($html);

        return $html;
    }


    protected function injectCompiledBlocks(string $html): string
    {
        $replacements = [];

        foreach ($this->blocks as $block) {
            $replacements[$block->signature()] = $block->compile();
        }

        return strtr($html, $replacements);
    }
}
