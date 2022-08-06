<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Actions\Constructors\FindsTitleForPage;

trait PageSchema
{
    /** @example "Home", "About", "Blog Feed" */
    public string $title;

    protected function constructPageSchema(): void
    {
        $this->title = FindsTitleForPage::run($this);
    }
}
