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
     * @var array<string, \Hyde\Pages\HybridPages\HybridPageBlock
     */
    protected array $blocks = [];

    public function handle(HybridPage $page): string
    {
        $markdown = $page->markdown;

        [$this->blocks, $markdown] = $this->extractBlocks($markdown->body());

        $html = Markdown::render($markdown, $page::class);

        $html = $this->injectCompiledBlocks($html);

        return $html;
    }

    /** @return array{array<string, \Hyde\Pages\HybridPages\HybridPageBlock>, string} */
    protected function extractBlocks(string $markdown): array
    {
        //
    }

    protected function makeBlock(string $info, string $content): ?HybridPageBlock
    {
        if ($info === 'blade') {
            return new BladePageBlock($this->page, $content);
        }

        if (preg_match('/^component\((?<name>[^)]+)\)$/', $info, $matches)) {
            return new ComponentPageBlock($this->page, trim($matches['name']), $content);
        }

        return null; // Not a hybrid block — leave it in the Markdown untouched.
    }

    protected function injectCompiledBlocks(string $html): string
    {
        $replacements = [];

        foreach ($this->blocks as $block) {
            $replacements[$block->signature()] = $block->render();
        }

        return strtr($html, $replacements);
    }
}
