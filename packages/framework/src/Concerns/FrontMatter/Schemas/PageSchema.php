<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Actions\Constructors\FindsNavigationDataForPage;
use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use JetBrains\PhpStorm\ArrayShape;

trait PageSchema
{
    /**
     * The title of the page used in the HTML <title> tag, among others.
     *
     * @example "Home", "About", "Blog Feed"
     */
    public string $title;

    #[ArrayShape(['title' => 'string', 'hidden' => 'bool', 'priority' => 'int'])]
    public ?array $navigation = null;

    protected function constructPageSchema(): void
    {
        $this->title = FindsTitleForPage::run($this);
        $this->navigation = FindsNavigationDataForPage::run($this);
    }
}
