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

    /**
     * The settings for how the page should be presented in the navigation menu.
     */
    #[ArrayShape(['title' => 'string', 'hidden' => 'bool', 'priority' => 'int'])]
    public ?array $navigation = null;

    /**
     * The canonical URL of the page.
     * @var string|null
     */
    public ?string $canonicalUrl = null;

    protected function constructPageSchema(): void
    {
        $this->title = FindsTitleForPage::run($this);
        $this->navigation = FindsNavigationDataForPage::run($this);
    }
}
