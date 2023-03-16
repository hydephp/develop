<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;

use function array_flip;
use function in_array;
use function is_a;

/**
 * Discover data used for navigation menus and the documentation sidebar.
 */
class NavigationDataFactory extends Concerns\PageDataFactory implements NavigationSchema
{
    /**
     * The front matter properties supported by this factory.
     *
     * Note that this represents a sub-schema, and is used as part of the page schema.
     */
    final public const SCHEMA = NavigationSchema::NAVIGATION_SCHEMA;

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

    /**
     * @return array{label: string|null, group: string|null, hidden: bool|null, priority: int|null}
     */
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
            ?? $this->getMatter('title')
            ?? $this->title;
    }

    protected function makeGroup(): ?string
    {
        if ($this->pageIsInSubdirectory() && $this->canUseSubdirectoryForGroups()) {
            return $this->getSubdirectoryName();
        }

        return $this->searchForGroupInFrontMatter() ?? $this->defaultGroup();
    }

    protected function makeHidden(): bool
    {
        return $this->isInstanceOf(MarkdownPost::class)
            || $this->searchForHiddenInFrontMatter()
            || in_array($this->routeKey, Config::getArray('hyde.navigation.exclude', ['404']))
            || ! $this->isInstanceOf(DocumentationPage::class) && $this->pageIsInSubdirectory() && ($this->getSubdirectoryConfiguration() === 'hidden');
    }

    protected function makePriority(): int
    {
        return $this->searchForPriorityInFrontMatter()
            ?? $this->searchForPriorityInConfigs()
            ?? self::FALLBACK_PRIORITY;
    }

    private function searchForLabelInFrontMatter(): ?string
    {
        return $this->getMatter('navigation.label')
            ?? $this->getMatter('navigation.title');
    }

    private function searchForGroupInFrontMatter(): ?string
    {
        return $this->getMatter('navigation.group')
            ?? $this->getMatter('navigation.category');
    }

    private function searchForHiddenInFrontMatter(): ?bool
    {
        return $this->getMatter('navigation.hidden')
            ?? $this->invert($this->getMatter('navigation.visible'));
    }

    private function searchForPriorityInFrontMatter(): ?int
    {
        return $this->getMatter('navigation.priority')
            ?? $this->getMatter('navigation.order');
    }

    private function searchForLabelInConfig(): ?string
    {
        return Arr::get(Config::getArray('hyde.navigation.labels', [
            'index' => 'Home',
            DocumentationPage::homeRouteName() => 'Docs',
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
        // TODO: In the future we could normalize this with the standard navigation config so both strategies can be auto-detected and used.

        // Adding an offset makes so that pages with a front matter priority that is lower can be shown first.
        // This is all to make it easier to mix ways of adding priorities.

        return $this->offset(Arr::get(
            array_flip(Config::getArray('docs.sidebar_order', [])), $this->identifier),
            self::CONFIG_OFFSET
        );
    }

    private function searchForPriorityInNavigationConfig(): ?int
    {
        return Config::getArray('hyde.navigation.order', [
            'index' => 0,
            'posts' => 10,
            'docs/index' => 100,
        ])[$this->routeKey] ?? null;
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
        return Config::getString('hyde.navigation.subdirectories', 'hidden');
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

    protected function getMatter(string $key): string|null|int|bool
    {
        return $this->matter->get($key);
    }
}
