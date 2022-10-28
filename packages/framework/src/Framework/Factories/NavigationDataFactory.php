<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Markdown\Models\FrontMatter;
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
    use InteractsWithFrontMatter;

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

    public function __construct(private FrontMatter $matter, private string $identifier, private string $routeKey, private string $title)
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
        return $this->findNavigationMenuLabel();
    }

    protected function makeGroup(): ?string
    {
        return $this->findNavigationMenuGroup();
    }

    protected function makeHidden(): ?bool
    {
        return $this->findNavigationMenuHidden();
    }

    protected function makePriority(): ?int
    {
        return $this->findNavigationMenuPriority();
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

    private function findNavigationMenuGroup(): ?string
    {
        if ($this->page instanceof DocumentationPage) {
            return $this->getDocumentationPageGroup();
        }

        return null;
    }

    private function findNavigationMenuHidden(): bool
    {
        if ($this->page instanceof MarkdownPost) {
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
        return $this->page instanceof DocumentationPage
            ? $this->findNavigationMenuPriorityInSidebarConfig(array_flip(config('docs.sidebar_order', []))) ?? self::FALLBACK_PRIORITY
            : $this->findNavigationMenuPriorityInNavigationConfig(config('hyde.navigation.order', [])) ?? self::FALLBACK_PRIORITY;
    }

    private function findNavigationMenuPriorityInNavigationConfig(array $config): ?int
    {
        return array_key_exists($this->routeKey, $config) ? (int) $config[$this->routeKey] : null;
    }

    private function findNavigationMenuPriorityInSidebarConfig(array $config): ?int
    {
        // Sidebars uses a special syntax where the keys are just the page identifiers in a flat array

        // Adding 250 makes so that pages with a front matter priority that is lower can be shown first.
        // It's lower than the fallback of 500 so that the config ones still come first.
        // This is all to make it easier to mix ways of adding priorities.

        return isset($config[$this->identifier])
            ? $config[$this->identifier] + (self::CONFIG_OFFSET)
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
        return str_contains($this->identifier, '/')
            // then we can use that as the category name.
            ? Str::before($this->identifier, '/')
            // Otherwise, we look in the front matter.
            : $this->matter('navigation.group', 'other');
    }
}
