<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use function array_flip;
use function config;
use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use function in_array;
use function is_a;

/**
 * Discover data used for navigation menus and the documentation sidebar.
 */
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
    private readonly string $title;
    private readonly string $routeKey;
    private readonly string $pageClass;
    private readonly string $identifier;
    private readonly FrontMatter $matter;

    public function __construct(CoreDataObject $pageData, string $title)
    {
        $this->matter = $pageData->matter;
        $this->identifier = $pageData->identifier;
        $this->pageClass = $pageData->pageClass;
        $this->routeKey = $pageData->routeKey;
        $this->title = $title;

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
        return $this->searchForLabelInFrontMatter()
            ?? $this->searchForLabelInConfig()
            ?? $this->matter('title')
            ?? $this->title;
    }

    protected function makeGroup(): ?string
    {
        if ($this->pageIsInSubdirectory()) {
            if ($this->canUseSubdirectoryForGroups()) {
                return $this->getSubdirectoryName();
            }
        }

        return $this->searchForGroupInFrontMatter() ?? $this->defaultGroup();
    }

    protected function makeHidden(): ?bool
    {
        return $this->isInstanceOf(MarkdownPost::class)
            || $this->searchForHiddenInFrontMatter()
            || in_array($this->routeKey, config('hyde.navigation.exclude', ['404']))
            || ($this->pageIsInSubdirectory() && ($this->getSubdirectoryConfiguration() === 'hidden'));
    }

    protected function makePriority(): int
    {
        return $this->searchForPriorityInFrontMatter()
            ?? $this->searchForPriorityInConfigs()
            ?? self::FALLBACK_PRIORITY;
    }

    private function searchForLabelInFrontMatter(): ?string
    {
        return $this->matter('navigation.label')
            ?? $this->matter('navigation.title');
    }

    private function searchForGroupInFrontMatter(): ?string
    {
        return $this->matter('navigation.group')
            ?? $this->matter('navigation.category');
    }

    private function searchForHiddenInFrontMatter(): ?bool
    {
        return $this->matter('navigation.hidden')
            ?? $this->invert($this->matter('navigation.visible'));
    }

    private function searchForPriorityInFrontMatter(): ?int
    {
        return $this->matter('navigation.priority')
            ?? $this->matter('navigation.order');
    }

    private function searchForLabelInConfig(): ?string
    {
        return Arr::get(config('hyde.navigation.labels', [
            'index' => 'Home',
            'docs/index' => 'Docs',
        ]), $this->routeKey);
    }

    private function searchForPriorityInConfigs(): ?int
    {
        return $this->isInstanceOf(DocumentationPage::class)
            ? $this->searchForPriorityInSidebarConfig()
            : $this->searchForPriorityInNavigationConfig();
    }

    private function searchForPriorityInSidebarConfig(): ?int
    {
        // Sidebars uses a special syntax where the keys are just the page identifiers in a flat array.
        // TODO: In the future we could normalize this with the standard navigation config so both strategies can be autodetected and used.

        // Adding an offset makes so that pages with a front matter priority that is lower can be shown first.
        // This is all to make it easier to mix ways of adding priorities.

        return $this->offset(Arr::get(
            array_flip(config('docs.sidebar_order', [])), $this->identifier),
            self::CONFIG_OFFSET
        );
    }

    private function searchForPriorityInNavigationConfig(): ?int
    {
        return config("hyde.navigation.order.$this->routeKey");
    }

    private function canUseSubdirectoryForGroups(): bool
    {
        return $this->getSubdirectoryConfiguration() === 'dropdown'
            || $this->isInstanceOf(DocumentationPage::class);
    }

    private function defaultGroup(): ?string
    {
        return $this->isInstanceOf(DocumentationPage::class) ? 'other' : null;
    }

    private function pageIsInSubdirectory(): bool
    {
        return Str::contains($this->identifier, '/');
    }

    private function getSubdirectoryName(): string
    {
        return Str::before($this->identifier, '/');
    }

    protected function getSubdirectoryConfiguration(): string
    {
        return config('hyde.navigation.subdirectories', 'hidden');
    }

    protected function isInstanceOf(string $class): bool
    {
        return is_a($this->pageClass, $class, true);
    }

    protected function invert(?bool $value): ?bool
    {
        return $value === null ? null : ! $value;
    }

    protected function offset(?int $value, int $offset): ?int
    {
        return $value === null ? null : $value + $offset;
    }
}
