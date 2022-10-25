<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Contracts\FrontMatter\DocumentationPageSchema;
use Hyde\Framework\Models\Navigation\NavigationData;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Illuminate\Support\Str;

/**
 * @internal Trait for HydePages to manage data used for navigation menus and the documentation sidebar.
 *
 * @see \Hyde\Framework\Concerns\HydePage
 */
trait GeneratesNavigationData
{
    protected function constructNavigationData(): NavigationData
    {
        if ($this instanceof DocumentationPageSchema) {
            return $this->makeNavigationData(
                $this->findNavigationMenuLabel(),
                $this->findNavigationMenuHidden(),
                $this->matter('navigation.priority', $this->findNavigationMenuPriority()),
                $this->getDocumentationPageGroup()
            );
        }

        return $this->makeNavigationData(
            $this->findNavigationMenuLabel(),
            $this->findNavigationMenuHidden(),
            $this->findNavigationMenuPriority(),
        );
    }

    protected function makeNavigationData(string $label, bool $hidden, int $priority, ?string $group = null): NavigationData
    {
        return NavigationData::make([
            'label' => $label,
            'group' => $group,
            'hidden' => $hidden,
            'priority' => $priority,
        ]);
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
        return array_key_exists($this->routeKey, $config) ? (int) $config[$this->routeKey] : null;
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

    private function getDocumentationPageGroup(): ?string
    {
        // If the documentation page is in a subdirectory,
        return str_contains($this->identifier, '/')
            // then we can use that as the category name.
            ? Str::before($this->identifier, '/')
            // Otherwise, we look in the front matter.
            : $this->matter('navigation.group', 'other');
    }
}
