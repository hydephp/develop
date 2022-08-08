<?php

namespace Hyde\Framework\Actions\Constructors;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Contracts\AbstractPage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use JetBrains\PhpStorm\ArrayShape;

class FindsNavigationDataForPage
{
    #[ArrayShape(['title' => 'string', 'hidden' => 'bool', 'priority' => 'int'])]
    public static function run(AbstractPage $page): array
    {
        return (new static($page))->getData();
    }

    protected function __construct(protected AbstractPage $page)
    {
    }

    #[ArrayShape(['title' => 'string', 'hidden' => 'bool', 'priority' => 'int'])]
    protected function getData(): array
    {
        return [
            'title' => $this->getNavigationMenuTitle(),
            'hidden' => ! $this->getNavigationMenuVisible(),
            'priority' => $this->getNavigationMenuPriority(),
        ];
    }

    protected function getNavigationMenuTitle(): string
    {
        if ($this->page->matter('navigation.title') !== null) {
            return $this->page->matter('navigation.title');
        }

        if ($this->page->identifier === 'index') {
            if ($this->page instanceof DocumentationPage) {
                return config('hyde.navigation.labels.docs', 'Docs');
            }

            return config('hyde.navigation.labels.home', 'Home');
        }

        if ($this->page->matter('title') !== null) {
            return $this->page->matter('title');
        }

        return $this->page->title;
    }

    protected function getNavigationMenuVisible(): bool
    {
        if ($this->page instanceof MarkdownPost) {
            return false;
        }

        if ($this->page instanceof DocumentationPage) {
            return $this->page->identifier === 'index' && ! in_array('docs', config('hyde.navigation.exclude', []));
        }

        if ($this->page instanceof AbstractMarkdownPage) {
            if ($this->page->matter('navigation.hidden', false)) {
                return false;
            }
        }

        if (in_array($this->page->identifier, config('hyde.navigation.exclude', ['404']))) {
            return false;
        }

        return true;
    }

    protected function getNavigationMenuPriority(): int
    {
        if ($this->page instanceof AbstractMarkdownPage) {
            if ($this->page->matter('navigation.priority') !== null) {
                return $this->page->matter('navigation.priority');
            }
        }

        if ($this->page instanceof DocumentationPage) {
            return (int) config('hyde.navigation.order.docs', 100);
        }

        if ($this->page->identifier === 'index') {
            return (int) config('hyde.navigation.order.index', 0);
        }

        if ($this->page->identifier === 'posts') {
            return (int) config('hyde.navigation.order.posts', 10);
        }

        if (array_key_exists($this->page->identifier, config('hyde.navigation.order', []))) {
            return (int) config('hyde.navigation.order.'.$this->page->identifier);
        }

        return 999;
    }
}
