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

        [$this->blocks, $markdown] = $this->extractBlocks($markdown->body());

        $html = Markdown::render($markdown, $this->page::class);

        $html = $this->injectCompiledBlocks($html);

        return $html;
    }

    /** @return array{array<string, \Hyde\Pages\HybridPages\HybridPageBlock>, string} */
    protected function extractBlocks(string $markdown): array
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $markdown));
        $count = count($lines);

        $blocks = [];
        $output = [];

        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];

            // Only opening code fences are interesting; everything else passes through.
            if (! preg_match('/^(?<indent> {0,3})(?<fence>`{3,}|~{3,})(?<info>.*)$/', $line, $open)) {
                $output[] = $line;
                continue;
            }

            $char = $open['fence'][0];
            $length = strlen($open['fence']);

            // A backtick info string may not itself contain backticks (CommonMark).
            if ($char === '`' && str_contains($open['info'], '`')) {
                $output[] = $line;
                continue;
            }

            $indent = strlen($open['indent']);
            $info = trim($open['info']);
            $closer = '/^ {0,3}('.$char.'{3,})[ \t]*$/';

            // Find the matching close: same char, at least as long, whitespace only.
            // Because a longer fence never closes on a shorter one, a ```` block
            // transparently contains ``` blocks — that is the whole escaping story.
            $body = [];
            $end = $i;
            for ($j = $i + 1; $j < $count; $j++) {
                if (preg_match($closer, $lines[$j], $m) && strlen($m[1]) >= $length) {
                    $end = $j;
                    break;
                }
                $body[] = $lines[$j];
            }
            $closed = $end > $i;

            if ($block = $this->makeBlock($info, $this->dedent($body, $indent))) {
                $blocks[$block->hash()] = $block;
                $output[] = $block->signature();
            } else {
                // Ordinary code block — emit it verbatim, fences and all.
                $output[] = $line;
                array_push($output, ...$body);

                if ($closed) {
                    $output[] = $lines[$end];
                }
            }

            // Resume after the close (or at EOF for an unterminated fence).
            $i = $closed ? $end : $count;
        }

        return [$blocks, implode("\n", $output)];
    }

    protected function dedent(array $lines, int $indent): string
    {
        if ($indent === 0) {
            return implode("\n", $lines);
        }

        return implode("\n", array_map(
            fn (string $line): string => preg_replace('/^ {0,'.$indent.'}/', '', $line),
            $lines,
        ));
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
