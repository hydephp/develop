<?php

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\AbstractMarkdownPage;
use Hyde\Framework\Hyde;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * Offloads logic related to navigation menu items for AbstractPage classes.
 *
 * @see \Hyde\Framework\Testing\Feature\Concerns\CanBeInNavigationTest
 */
trait CanBeInNavigation
{
    /**
     * Should the item should be displayed in the navigation menu?
     *
     * @return bool
     */
    public function showInNavigation(): bool
    {
        if ($this instanceof MarkdownPost) {
            return false;
        }

        if ($this instanceof DocumentationPage) {
            return $this->slug === 'index';
        }

        if ($this instanceof AbstractMarkdownPage) {
            if ($this->markdown->matter('navigation.hidden', false)) {
                return false;
            }
        }

        if (in_array($this->slug, config('hyde.navigation.exclude', ['404']))) {
            return false;
        }

        return true;
    }

    /**
     * The relative priority, determining the position of the item in the menu.
     *
     * @return int
     */
    public function navigationMenuPriority(): int
    {
        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter('navigation.priority') !== null) {
                return $this->matter('navigation.priority');
            }
        }

        if (array_key_exists($this->slug, config('hyde.navigation.order', []))) {
            return (int) config('hyde.navigation.order.'.$this->slug);
        }

        if ($this instanceof DocumentationPage) {
            return 100;
        }

        if ($this->slug === 'index') {
            return 0;
        }

        if ($this->slug === 'posts') {
            return 10;
        }

        return 999;
    }

    /**
     * The page title to display in the navigation menu.
     *
     * @return string
     */
    public function navigationMenuTitle(): string
    {
        if ($this instanceof AbstractMarkdownPage) {
            if ($this->matter('navigation.title') !== null) {
                return $this->matter('navigation.title');
            }

            if ($this->matter('title') !== null) {
                return $this->matter('title');
            }
        }

        if ($this->slug === 'index') {
            if ($this instanceof DocumentationPage) {
                return 'Docs';
            }

            return 'Home';
        }

        if (isset($this->title) && ! blank($this->title)) {
            return $this->title;
        }

        return Hyde::makeTitle($this->slug);
    }

    /**
     * Not yet implemented.
     *
     * If an item returns a route collection,
     * it will automatically be made into a dropdown.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Modules\Routing\Route>
     */
    // public function navigationMenuChildren(): Collection;
}
