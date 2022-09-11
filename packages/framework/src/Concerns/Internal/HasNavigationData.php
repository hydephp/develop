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
            $this->findNavigationMenuHidden(),
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

        if ($this->matter('navigation.hidden', false)) {
            return true;
        }

        if (in_array($this->routeKey, config('hyde.navigation.exclude', ['404']))) {
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

        // Different default return values are to preserve backwards compatibility
        return $this instanceof DocumentationPage
            ? $this->findNavigationMenuPriorityInSidebarConfig(array_flip(config('docs.sidebar_order', []))) ?? 500
            : $this->findNavigationMenuPriorityInNavigationConfig(config('hyde.navigation.order', [])) ?? 999;
    }

    private function findNavigationMenuPriorityInNavigationConfig(array $config): ?int
    {
        return array_key_exists($this->routeKey, $config) ? (int)$config[$this->routeKey] : null;
    }

    private function findNavigationMenuPriorityInSidebarConfig(array $config): ?int
    {
        // Sidebars uses a special syntax where the keys are just the page identifiers in a flat array
        return isset($config[$this->identifier]) ? $config[$this->identifier] + 250 : null;
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
}
