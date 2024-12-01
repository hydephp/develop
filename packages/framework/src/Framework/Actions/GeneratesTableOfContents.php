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

    public function execute(): string
    {
        $headings = $this->parseHeadings();

        return view('hyde::components.docs.table-of-contents', [
            'items' => $this->buildTableOfContents($headings),
        ])->render();
    }

    protected function parseHeadings(): array
    {
        preg_match_all('/^#{2,4}\s+(.+)$/m', $this->markdown, $matches);
        
        $headings = [];
        foreach ($matches[0] as $index => $heading) {
            $level = substr_count($heading, '#');
            $title = $matches[1][$index];
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
