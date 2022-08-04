<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Models\Pages\DocumentationPage;

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
        $this->page->title = SourceFileParser::findTitleForPage($this->page, $this->page->identifier);

        if ($this->page instanceof DocumentationPage) {
            $this->page->category = SourceFileParser::getDocumentationPageCategory($this->page, $this->page->identifier);
        }
    }

    protected function get(): AbstractPage
    {
        return $this->page;
    }
}
