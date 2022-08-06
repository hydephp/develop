<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Models\Pages\BladePage;

/**
 * Dynamically constructs data for a page model.
 *
 * @see \Hyde\Framework\Testing\Feature\PageModelConstructorTest
 */
class PageModelConstructor
{
    /**
     * @var AbstractPage|AbstractMarkdownPage|BladePage
     */
    protected AbstractPage|AbstractMarkdownPage|BladePage $page;

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
        //
    }

    protected function get(): AbstractPage
    {
        return $this->page;
    }
}
