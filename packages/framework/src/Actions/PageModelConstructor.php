<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Illuminate\Support\Str;

/**
 * Dynamically constructs data for a page model.
 */
class PageModelConstructor
{
    protected AbstractPage $page;

    public static function run(AbstractPage $page): AbstractPage
    {
        return (new static($page))->get();
    }

    protected function __construct(AbstractPage $page)
    {
        $this->page = $page;
        $this->constructDynamicData();
    }

    protected function constructDynamicData(): void
    {
        $this->page->title = self::findTitleForPage($this->page, $this->page->identifier);

        if ($this->page instanceof DocumentationPage) {
            $this->page->category = self::getDocumentationPageCategory($this->page, $this->page->identifier);
        }
    }

    protected function get(): AbstractPage
    {
        return $this->page;
    }

    protected function getDocumentationPageCategory(DocumentationPage $page, string $slug): ?string
    {
        // If the documentation page is in a subdirectory,
        // then we can use that as the category name.
        // Otherwise, we look in the front matter.

        return str_contains($slug, '/')
            ? Str::before($slug, '/')
            : $page->matter('category');
    }

    protected function findTitleForPage(BladePage|AbstractMarkdownPage $page, string $slug): string
    {
        if ($page instanceof BladePage) {
            return Hyde::makeTitle($slug);
        }

        if ($page->matter('title')) {
            return $page->matter('title');
        }

        return static::findTitleFromMarkdownHeadings($page) ?? Hyde::makeTitle($slug);
    }

    protected function findTitleFromMarkdownHeadings(AbstractMarkdownPage $page): ?string
    {
        foreach ($page->markdown()->toArray() as $line) {
            if (str_starts_with($line, '# ')) {
                return trim(substr($line, 2), ' ');
            }
        }

        return null;
    }
}
