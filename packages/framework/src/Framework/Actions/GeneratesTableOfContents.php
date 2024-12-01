<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Facades\Config;
use Hyde\Markdown\Models\Markdown;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

class GeneratesTableOfContents
{
    protected string $markdown;

    public function __construct(Markdown|string $markdown)
    {
        $this->markdown = (string) $markdown;
    }

    public function execute(): array
    {
        $headings = $this->parseHeadings();

        return $this->buildTableOfContents($headings);
    }

    protected function parseHeadings(): array
    {
        // Match both ATX-style (###) and Setext-style (===, ---) headers
        $pattern = '/^(?:#{2,4}\s+(.+)|(.+)\n([=\-])\3+)$/m';
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
                if ($level < Config::getInt('docs.sidebar.table_of_contents.min_heading_level', 2)) {
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
        $minLevel = Config::getInt('docs.sidebar.table_of_contents.min_heading_level', 2);
        $maxLevel = Config::getInt('docs.sidebar.table_of_contents.max_heading_level', 4);

        $items = [];
        $stack = [&$items];
        $previousLevel = $minLevel;

        foreach ($headings as $heading) {
            if ($heading['level'] < $minLevel || $heading['level'] > $maxLevel) {
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
                array_splice($stack, $heading['level'] - $minLevel + 1);
            }

            $stack[count($stack) - 1][] = $item;
            $previousLevel = $heading['level'];
        }

        return $items;
    }
}
