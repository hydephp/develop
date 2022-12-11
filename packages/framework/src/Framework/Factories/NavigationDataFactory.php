<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use function array_flip;
use function array_key_exists;
use function array_merge;
use function config;
use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPost;
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
            if ($this->getSubdirectoryConfiguration() === 'dropdown' || $this->isInstanceOf(DocumentationPage::class)) {
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
        return $this->defaultLabelConfiguration()[$this->routeKey] ?? null;
    }

    private function searchForPriorityInConfigs(): ?int
    {
        return $this->isInstanceOf(DocumentationPage::class)
            ? $this->searchForPriorityInSidebarConfig()
            : $this->searchForPriorityInNavigationConfig();
    }

    private function searchForPriorityInSidebarConfig(): ?int
    {
        // Sidebars uses a special syntax where the keys are just the page identifiers in a flat array

        // Adding 250 makes so that pages with a front matter priority that is lower can be shown first.
        // It's lower than the fallback of 500 so that the config ones still come first.
        // This is all to make it easier to mix ways of adding priorities.

        $config = array_flip(config('docs.sidebar_order', []));
        return isset($config[$this->identifier])
            ? $config[$this->identifier] + (self::CONFIG_OFFSET)
            : null;
    }

    private function searchForPriorityInNavigationConfig(): ?int
    {
        $config = config('hyde.navigation.order', []);
        return array_key_exists($this->routeKey, $config)
            ? (int) $config[$this->routeKey]
            : null;
    }

    private function defaultLabelConfiguration(): array
    {
        return array_merge([
            'index' => 'Home',
            'docs/index' => 'Docs',
        ], config('hyde.navigation.labels', []));
    }

    private function defaultGroup(): ?string
    {
        if ($this->isInstanceOf(DocumentationPage::class)) {
            return 'other';
        }

        return null;
    }

    private function getSubdirectoryConfiguration(): string
    {
        return config('hyde.navigation.subdirectories', 'hidden');
    }

    private function pageIsInSubdirectory(): bool
    {
        return Str::contains($this->identifier, '/');
    }

    private function getSubdirectoryName(): string
    {
        return Str::before($this->identifier, '/');
    }

    protected function isInstanceOf(string $class): bool
    {
        return is_a($this->pageClass, $class, true);
    }

    protected function invert(?bool $value): ?bool
    {
        return $value === null ? null : ! $value;
    }
}
