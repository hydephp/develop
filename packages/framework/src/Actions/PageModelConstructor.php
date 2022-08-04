<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Contracts\AbstractPage;

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
    }

    protected function get(): AbstractPage
    {
        return $this->page;
    }
}
