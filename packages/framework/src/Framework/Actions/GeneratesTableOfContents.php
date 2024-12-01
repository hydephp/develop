<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Facades\Config;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Str;

class GeneratesTableOfContents
{
    protected string $markdown;

    protected int $minHeadingLevel = 2;
    protected int $maxHeadingLevel = 4;

    public function __construct(Markdown|string $markdown)
    {
        $this->markdown = (string) $markdown;
        $this->minHeadingLevel = Config::getInt('docs.sidebar.table_of_contents.min_heading_level', 2);
        $this->maxHeadingLevel = Config::getInt('docs.sidebar.table_of_contents.max_heading_level', 4);
    }

    public function execute(): array
    {
        $headings = $this->parseHeadings();

        return $this->buildTableOfContents($headings);
    }

    protected function parseHeadings(): array
    {
        // Match both ATX-style (###) and Setext-style (===, ---) headers
        $pattern = '/^(?:#{1,6}\s+(.+)|(.+)\n([=\-])\3+)$/m';
        preg_match_all($pattern, $this->markdown, $matches);

        $headings = [];
        foreach ($matches[0] as $index => $heading) {
            // Handle ATX-style headers (###)
            if (str_starts_with($heading, '#')) {
                $level = substr_count($heading, '#');
                $title = $matches[1][$index];
            }
            // Handle Setext-style headers (=== or ---)
            else {
                $title = trim($matches[2][$index]);
                $level = $matches[3][$index] === '=' ? 1 : 2;
                // Only add if the config level is met
                if ($level < $this->minHeadingLevel) {
                    continue;
                }
            }

            $slug = Str::slug($title);
            $headings[] = [
                'level' => $level,
                'title' => $title,
                'slug' => $slug,
            ];
        }

        return $headings;
    }

    protected function buildTableOfContents(array $headings): array
    {
        $items = [];
        $stack = [&$items];
        $previousLevel = $this->minHeadingLevel;

        foreach ($headings as $heading) {
            if ($heading['level'] < $this->minHeadingLevel || $heading['level'] > $this->maxHeadingLevel) {
                continue;
            }

            $item = [
                'title' => $heading['title'],
                'slug' => $heading['slug'],
                'children' => [],
            ];

            if ($heading['level'] > $previousLevel) {
                $stack[] = &$stack[count($stack) - 1][count($stack[count($stack) - 1]) - 1]['children'];
            } elseif ($heading['level'] < $previousLevel) {
                array_splice($stack, $heading['level'] - $this->minHeadingLevel + 1);
            }

            $stack[count($stack) - 1][] = $item;
            $previousLevel = $heading['level'];
        }

        return $items;
    }
}
