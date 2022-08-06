<?php

namespace Hyde\Framework\Actions\Constructors;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Hyde;

/**
 * @see \Hyde\Framework\Testing\Feature\PageModelConstructorTest
 *
 * @internal
 */
class FindsTitleForPage
{
    protected AbstractPage $page;

    public static function run(AbstractPage $page): string
    {
        return (new static($page))->findTitleForPage();
    }

    protected function __construct(AbstractPage $page)
    {
        $this->page = $page;
    }

    protected function findTitleForPage(): string
    {
        return $this->page instanceof AbstractMarkdownPage
            ? $this->findTitleForMarkdownPage()
            : Hyde::makeTitle($this->page->identifier);
    }

    protected function findTitleForMarkdownPage(): string
    {
        return $this->page->matter('title')
            ?? $this->findTitleFromMarkdownHeadings()
            ?? Hyde::makeTitle($this->page->identifier);
    }

    protected function findTitleFromMarkdownHeadings(): ?string
    {
        if ($this->page instanceof AbstractMarkdownPage) {
            foreach ($this->page->markdown()->toArray() as $line) {
                if (str_starts_with($line, '# ')) {
                    return trim(substr($line, 2), ' ');
                }
            }
        }

        return null;
    }
}
