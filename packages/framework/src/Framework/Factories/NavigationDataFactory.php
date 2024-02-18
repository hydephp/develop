<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Facades\Config;
use Illuminate\Support\Str;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;

use function basename;
use function array_flip;
use function in_array;
use function is_a;
use function array_key_exists;

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
            || $this->isPageHiddenInNavigationConfiguration()
            || $this->isNonDocumentationPageInHiddenSubdirectory();
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

    private function isPageHiddenInNavigationConfiguration(): ?bool
    {
        return in_array($this->routeKey, Config::getArray('hyde.navigation.exclude', ['404']));
    }

    private function isNonDocumentationPageInHiddenSubdirectory(): bool
    {
        return ! $this->isInstanceOf(DocumentationPage::class)
            && $this->pageIsInSubdirectory()
            && $this->getSubdirectoryConfiguration() === 'hidden'
            && basename($this->identifier) !== 'index';
    }

    private function searchForPriorityInFrontMatter(): ?int
    {
        return $this->getMatter('navigation.priority')
            ?? $this->getMatter('navigation.order');
    }

    private function searchForLabelInConfig(): ?string
    {
        /** @var array<string, string> $config */
        $config = Config::getArray('hyde.navigation.labels', [
            'index' => 'Home',
            DocumentationPage::homeRouteName() => 'Docs',
        ]);

        return $config[$this->routeKey] ?? null;
    }

    private function searchForPriorityInConfigs(): ?int
    {
        return $this->isInstanceOf(DocumentationPage::class)
            ? $this->searchForPriorityInSidebarConfig()
            : $this->searchForPriorityInNavigationConfig();
    }

    private function searchForPriorityInSidebarConfig(): ?int
    {
        /** @var array<string>|array<string, int> $config */
        $config = Config::getArray('docs.sidebar_order', []);

        return
            // For consistency with the navigation config.
            $this->parseNavigationPriorityConfig($config, 'routeKey')
            // For backwards compatibility, and ease of use, as the route key prefix
            // is redundant due to it being the same for all documentation pages
            ?? $this->parseNavigationPriorityConfig($config, 'identifier');
    }

    private function searchForPriorityInNavigationConfig(): ?int
    {
        /** @var array<string, int>|array<string> $config */
        $config = Config::getArray('hyde.navigation.order', [
            'index' => 0,
            'posts' => 10,
            'docs/index' => 100,
        ]);

        return $this->parseNavigationPriorityConfig($config, 'routeKey');
    }

    /** @param array<string, int>|array<string> $config */
    private function parseNavigationPriorityConfig(array $config, string $pageKeyName): ?int
    {
        $pageKey = $this->{$pageKeyName};

        // Check if the config entry is a flat array or a keyed array.
        if (! array_key_exists($pageKey, $config)) {
            // Adding an offset makes so that pages with a front matter priority, or
            // explicit keyed priority selection that is lower can be shown first.
            // This is all to make it easier to mix ways of adding priorities.

            return $this->offset(
                array_flip($config)[$pageKey] ?? null,
                self::CONFIG_OFFSET
            );
        }

        return $config[$pageKey] ?? null;
    }

    private function canUseSubdirectoryForGroups(): bool
    {
        return $this->getSubdirectoryConfiguration() === 'dropdown'
            || $this->isInstanceOf(DocumentationPage::class);
    }

    private function defaultGroup(): ?string
    {
        return $this->isInstanceOf(DocumentationPage::class) ? null : null;
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

    /** @param class-string $class */
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
