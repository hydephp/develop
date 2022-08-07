<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas;

use Hyde\Framework\Actions\Constructors\FindsTitleForPage;
use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
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

     #[ArrayShape(['title' => "string", 'hidden' => "bool", 'priority' => "int"])]
     protected function constructNavigation(): array
     {
         return [
             'title' => $this->getNavigationMenuTitle(),
             'hidden' => ! $this->getNavigationMenuVisible(),
             'priority' => $this->getNavigationMenuPriority(),
         ];
     }

    protected function getNavigationMenuTitle(): string
    {
        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter->get('navigation.title') !== null) {
                return $this->matter->get('navigation.title');
            }

            if ($this->matter->get('title') !== null) {
                return $this->matter->get('title');
            }
        }

        if ($this->identifier === 'index') {
            if ($this instanceof DocumentationPage) {
                return config('hyde.navigation.labels.docs', 'Docs');
            }

            return config('hyde.navigation.labels.home', 'Home');
        }

        return $this->title;
    }

    protected function getNavigationMenuVisible(): bool
    {
        if ($this instanceof MarkdownPost) {
            return false;
        }

        if ($this instanceof DocumentationPage) {
            return $this->identifier === 'index' && ! in_array('docs', config('hyde.navigation.exclude', []));
        }

        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter('navigation.hidden', false)) {
                return false;
            }
        }

        if (in_array($this->identifier, config('hyde.navigation.exclude', ['404']))) {
            return false;
        }

        return true;
    }

    protected function getNavigationMenuPriority(): int
    {
        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter('navigation.priority') !== null) {
                return $this->matter('navigation.priority');
            }
        }

        if ($this instanceof DocumentationPage) {
            return (int) config('hyde.navigation.order.docs', 100);
        }

        if ($this->identifier === 'index') {
            return (int) config('hyde.navigation.order.index', 0);
        }

        if ($this->identifier === 'posts') {
            return (int) config('hyde.navigation.order.posts', 10);
        }

        if (array_key_exists($this->identifier, config('hyde.navigation.order', []))) {
            return (int) config('hyde.navigation.order.'.$this->identifier);
        }

        return 999;
    }
}
