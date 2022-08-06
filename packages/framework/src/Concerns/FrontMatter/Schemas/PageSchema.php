<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Actions\Constructors\FindsTitleForPage;

trait PageSchema
{
    /**
     * The title of the page used in the HTML <title> tag, among others.
     * @example "Home", "About", "Blog Feed" 
     */
    public string $title;

    protected function constructPageSchema(): void
    {
        $this->title = FindsTitleForPage::run($this);
    }
}
