<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\Str;
use function array_flip;
use function array_key_exists;
use function array_merge;
use function config;
use function in_array;

class NavigationDataFactory extends Concerns\PageDataFactory implements NavigationSchema
{
    /**
     * The front matter properties supported by this factory.
     *
     * Note that this represents a sub-schema, and is used as part of the page schema.
     */
    public const SCHEMA = NavigationSchema::NAVIGATION_SCHEMA;

    protected const FALLBACK_PRIORITY = 999;
    protected const CONFIG_OFFSET = 500;

    protected readonly ?string $label;
    protected readonly ?string $group;
    protected readonly ?bool $hidden;
    protected readonly ?int $priority;

    public function __construct()
    {
        $this->label = $this->makeLabel();
        $this->group = $this->makeGroup();
        $this->hidden = $this->makeHidden();
        $this->priority = $this->makePriority();
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'group' => $this->group,
            'hidden' => $this->hidden,
            'priority' => $this->priority,
        ];
    }

    protected function makeLabel(): ?string
    {
        //
    }

    protected function makeGroup(): ?string
    {
        //
    }

    protected function makeHidden(): ?bool
    {
        //
    }

    protected function makePriority(): ?int
    {
        //
    }

    private function findNavigationMenuLabel(): string
    {
        if ($this->page->matter('navigation.label') !== null) {
            return $this->page->matter('navigation.label');
        }

        if (isset($this->getNavigationLabelConfig()[$this->page->routeKey])) {
            return $this->getNavigationLabelConfig()[$this->page->routeKey];
        }

        return $this->page->matter('title') ?? 'not refactored yet' ?? $this->page->title;
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
