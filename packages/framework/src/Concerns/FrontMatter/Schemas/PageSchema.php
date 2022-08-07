<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

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

    public ?array $navigation = null;

    protected function constructPageSchema(): void
    {
        $this->title = FindsTitleForPage::run($this);

        $this->navigation = $this->constructNavigation();
    }

    #[ArrayShape(['title' => "string", 'order' => "int"])] protected function constructNavigation(): array
    {
        return [
            'title' => $this->navigationMenuTitle(),
            'order' => $this->navigationMenuPriority(),
        ];
    }
}
