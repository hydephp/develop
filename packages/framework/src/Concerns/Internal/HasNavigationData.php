<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;

/**
 * @internal Trait for HydePages to manage data used for navigation menus and the documentation sidebar.
 */
trait HasNavigationData
{
    public function showInNavigation(): bool
    {
        return ! $this->navigation['hidden'];
    }

    public function navigationMenuPriority(): int
    {
        return $this->navigation['priority'];
    }

    public function navigationMenuLabel(): string
    {
        return $this->navigation['label'];
    }

    protected function constructNavigationData(): void
    {
        $this->setNavigationData(
            $this->findNavigationMenuLabel(),
            $this->findNavigationMenuHidden(),
            $this->findNavigationMenuPriority(),
        );
    }

    protected function constructSidebarNavigationData(): void
    {
        $this->setNavigationData(
            $this->findNavigationMenuLabel(),
            ! $this->matter('navigation.hidden', $this->shouldDocumentationPageBeVisible()),
            $this->matter('navigation.priority', $this->findNavigationMenuPriority())
        );
    }

    protected function setNavigationData(string $label, bool $hidden, int $priority): void
    {
        $this->navigation = [
            'label' => $label,
            'hidden' => $hidden,
            'priority' => $priority,
        ];
    }

    private function findNavigationMenuLabel(): string
    {
        if ($this->matter('navigation.label') !== null) {
            return $this->matter('navigation.label');
        }

        if (isset($this->getNavigationLabelConfig()[$this->routeKey])) {
            return $this->getNavigationLabelConfig()[$this->routeKey];
        }

        return $this->matter('title') ?? $this->title;
    }

    private function findNavigationMenuHidden(): bool
    {
        if ($this instanceof MarkdownPost) {
            return true;
        }

        if ($this instanceof DocumentationPage) {
            return ! ($this->shouldDocumentationPageBeVisible());
        }

        if ($this->matter('navigation.hidden', false)) {
            return true;
        }

        if (in_array($this->identifier, config('hyde.navigation.exclude', ['404']))) {
            return true;
        }

        return false;
    }

    private function findNavigationMenuPriority(): int
    {
        if ($this->matter('navigation.priority') !== null) {
            return $this->matter('navigation.priority');
        }

        $config = $this instanceof DocumentationPage
            ? config('docs.sidebar_order', [])
            : config('hyde.navigation.order', []);

        if (array_key_exists($this->routeKey, $config)) {
            return (int) $config[$this->routeKey];
        }

        if (! in_array($this->routeKey, $config)) {
            // Preserve backwards compatibility
            return $this instanceof DocumentationPage ? 500 : 999;
        }

        // Handle the case where the route key is in the config, but the value is not set
        return array_search($this->routeKey, $config) + 250;

        // Adding 250 makes so that pages with a front matter priority that is lower
        // can be shown first. It's lower than the fallback of 500 so that they
        // still come first. This is all to make it easier to mix priorities.
    }

    private function getNavigationLabelConfig(): array
    {
        return array_merge([
            'index' => 'Home',
            'docs/index' => 'Docs',
        ], config('hyde.navigation.labels', []));
    }

    private function shouldDocumentationPageBeVisible(): bool
    {
        return $this->identifier !== 'index' && ! in_array($this->routeKey, config('hyde.navigation.exclude', []));
    }
}
