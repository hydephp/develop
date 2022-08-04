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
        $this->page->title = self::findTitleForPage();

        if ($this->page instanceof DocumentationPage) {
            $this->page->category = self::getDocumentationPageCategory();
        }
    }

    protected function get(): AbstractPage
    {
        return $this->page;
    }

    protected function getDocumentationPageCategory(): ?string
    {
        // If the documentation page is in a subdirectory,
        // then we can use that as the category name.
        // Otherwise, we look in the front matter.

        return str_contains($this->page->identifier, '/')
            ? Str::before($this->page->identifier, '/')
            : $this->page->matter('category');
    }

    protected function findTitleForPage(): string
    {
        if ($this->page instanceof BladePage) {
            return Hyde::makeTitle($this->page->identifier);
        }

        if ($this->page->matter('title')) {
            return $this->page->matter('title');
        }

        return static::findTitleFromMarkdownHeadings() ?? Hyde::makeTitle($this->page->identifier);
    }

    protected function findTitleFromMarkdownHeadings(): ?string
    {
        foreach ($this->page->markdown()->toArray() as $line) {
            if (str_starts_with($line, '# ')) {
                return trim(substr($line, 2), ' ');
            }
        }

        return null;
    }
}
