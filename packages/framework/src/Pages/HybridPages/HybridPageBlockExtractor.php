<?php

declare(strict_types=1);

namespace Hyde\Pages\HybridPages;

use Hyde\Pages\HybridPage;

use function array_map;
use function array_push;
use function count;
use function explode;
use function implode;
use function preg_match;
use function preg_replace;
use function str_replace;
use function strlen;
use function trim;

class HybridPageBlockExtractor
{
    protected HybridPage $page;

    public function __construct(HybridPage $page)
    {
        $this->page = $page;
    }

    /** @return array{array<string, \Hyde\Pages\HybridPages\HybridPageBlock>, string} */
    public function handle(string $markdown): array
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
                $signature = $block->signature;
                $blocks[$signature] = $block;
                $output[] = $signature;
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
            return new ComponentPageBlock($this->page, $content, trim($matches['name']));
        }

        return null; // Not a hybrid block — leave it in the Markdown untouched.
    }
}
