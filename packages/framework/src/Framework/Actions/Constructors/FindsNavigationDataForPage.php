<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Constructors;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Navigation\NavigationData;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Contracts\FrontMatter\DocumentationPageSchema;
use Illuminate\Support\Str;

/**
 * Helper for HydePages to discover data used for navigation menus and the documentation sidebar.
 *
 * @internal
 *
 * @see \Hyde\Framework\Testing\Feature\PageModelConstructorsTest
 * @see \Hyde\Framework\Concerns\HydePage
 */
final class FindsNavigationDataForPage
{
    protected const FALLBACK_PRIORITY = 999;
    protected const CONFIG_OFFSET = 500;

    public static function run(HydePage $page): NavigationData
    {
        return (new self($page))->constructNavigationData();
    }

    protected function __construct(protected HydePage $page)
    {
    }

    protected function constructNavigationData(): NavigationData
    {
        if ($this->page instanceof DocumentationPageSchema) {
            return $this->makeNavigationData(
                $this->findNavigationMenuLabel(),
                $this->findNavigationMenuHidden(),
                $this->page->matter('navigation.priority', $this->findNavigationMenuPriority()),
                $this->getDocumentationPageGroup()
            );
        }

        return $this->makeNavigationData(
            $this->findNavigationMenuLabel(),
            $this->findNavigationMenuHidden(),
            $this->findNavigationMenuPriority(),
        );
    }

    private function makeNavigationData(string $label, bool $hidden, int $priority, ?string $group = null): NavigationData
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
        if ($this->page->matter('navigation.label') !== null) {
            return $this->page->matter('navigation.label');
        }

        if (isset($this->getNavigationLabelConfig()[$this->page->routeKey])) {
            return $this->getNavigationLabelConfig()[$this->page->routeKey];
        }

        return $this->page->matter('title') ?? $this->page->title;
    }

    private function findNavigationMenuHidden(): bool
    {
        if ($this->page instanceof MarkdownPost) {
            return true;
        }

        if ($this->page->matter('navigation.hidden', false)) {
            return true;
        }

        if (in_array($this->page->routeKey, config('hyde.navigation.exclude', ['404']))) {
            return true;
        }

        return false;
    }

    private function findNavigationMenuPriority(): int
    {
        if ($this->page->matter('navigation.priority') !== null) {
            return $this->page->matter('navigation.priority');
        }

        // Different default return values are to preserve backwards compatibility
        return $this->page instanceof DocumentationPage
            ? $this->findNavigationMenuPriorityInSidebarConfig(array_flip(config('docs.sidebar_order', []))) ?? self::FALLBACK_PRIORITY
            : $this->findNavigationMenuPriorityInNavigationConfig(config('hyde.navigation.order', [])) ?? self::FALLBACK_PRIORITY;
    }

    private function findNavigationMenuPriorityInNavigationConfig(array $config): ?int
    {
        return array_key_exists($this->page->routeKey, $config) ? (int) $config[$this->page->routeKey] : null;
    }

    private function findNavigationMenuPriorityInSidebarConfig(array $config): ?int
    {
        // Sidebars uses a special syntax where the keys are just the page identifiers in a flat array

        // Adding 250 makes so that pages with a front matter priority that is lower can be shown first.
        // It's lower than the fallback of 500 so that the config ones still come first.
        // This is all to make it easier to mix ways of adding priorities.

        return isset($config[$this->page->identifier])
            ? $config[$this->page->identifier] + (self::CONFIG_OFFSET)
            : null;
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
        return str_contains($this->page->identifier, '/')
            // then we can use that as the category name.
            ? Str::before($this->page->identifier, '/')
            // Otherwise, we look in the front matter.
            : $this->page->matter('navigation.group', 'other');
    }
}
