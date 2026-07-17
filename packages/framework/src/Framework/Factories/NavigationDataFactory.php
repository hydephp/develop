<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Facades\Config;
use Illuminate\Support\Str;
use Hyde\Pages\MarkdownPost;
use Hyde\Pages\DocumentationPage;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Framework\Features\Navigation\NumericalPageOrderingHelper;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

use function is_a;
use function in_array;
use function basename;
use function array_flip;
use function array_intersect;
use function array_key_exists;
use function str_ends_with;

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

    protected readonly ?string $label;
    protected readonly ?string $group;
    protected readonly ?bool $hidden;
    protected readonly ?int $priority;
    private readonly string $title;
    private readonly string $routeKey;
    private readonly string $outputPath;
    private readonly string $pageClass;
    private readonly string $identifier;
    private readonly FrontMatter $matter;

    /**
     * The keys the documentation configuration entries can use to target this page, most specific first.
     *
     * @var array<int, string>
     */
    private readonly array $configurationKeys;

    public function __construct(CoreDataObject $pageData, string $title)
    {
        $this->matter = $pageData->matter;
        $this->identifier = $pageData->identifier;
        $this->pageClass = $pageData->pageClass;
        $this->routeKey = $pageData->routeKey;
        $this->outputPath = $pageData->outputPath;
        $this->title = $title;

        $this->configurationKeys = $this->isInstanceOf(DocumentationPage::class)
            ? DocumentationVersions::configurationKeys($this->routeKey, $this->identifier)
            : [$this->routeKey];

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
            ?? $this->searchForLabelInConfigs()
            ?? $this->getMatter('title')
            ?? $this->title;
    }

    protected function makeGroup(): ?string
    {
        if ($this->pageIsInSubdirectory() && $this->canUseSubdirectoryForGroups()) {
            return NumericalPageOrderingHelper::hasNumericalPrefix($this->getSubdirectoryName())
                ? NumericalPageOrderingHelper::splitNumericPrefix($this->getSubdirectoryName())[1]
                : $this->getSubdirectoryName();
        }

        return $this->searchForGroupInFrontMatter();
    }

    protected function makeHidden(): bool
    {
        $frontMatterHidden = $this->searchForHiddenInFrontMatter();

        return $this->isInstanceOf(MarkdownPost::class)
            || $frontMatterHidden === true
            || $this->searchForHiddenInConfigs()
            || $this->isNonDocumentationPageInHiddenSubdirectory()
            || ($frontMatterHidden === null && $this->hasNonHtmlOutput());
    }

    private function hasNonHtmlOutput(): bool
    {
        return ! str_ends_with($this->outputPath, '.html');
    }

    protected function makePriority(): int
    {
        return $this->searchForPriorityInFrontMatter()
            ?? $this->searchForPriorityInConfigs()
            ?? $this->checkFilePrefixForOrder()
            ?? NavigationMenu::LAST;
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

    private function searchForHiddenInConfigs(): bool
    {
        // The main navigation configuration only targets pages by their route key, whereas the
        // sidebar configuration can also target documentation pages by their identifier.
        return in_array($this->routeKey, Config::getArray('hyde.navigation.exclude', ['404']), true)
            || ($this->isInstanceOf(DocumentationPage::class) && $this->isExcludedInSidebarConfig());
    }

    private function isExcludedInSidebarConfig(): bool
    {
        return array_intersect($this->configurationKeys, Config::getArray('docs.sidebar.exclude', ['404'])) !== [];
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

    private function searchForLabelInConfigs(): ?string
    {
        /** @var array<string, string> $config */
        $config = $this->isInstanceOf(DocumentationPage::class)
            ? Config::getArray('docs.sidebar.labels', [
                DocumentationPage::homeRouteName() => 'Docs',
            ])
            : Config::getArray('hyde.navigation.labels', [
                'index' => 'Home',
                DocumentationPage::homeRouteName() => 'Docs',
            ]);

        foreach ($this->configurationKeys as $key) {
            if (isset($config[$key])) {
                return $config[$key];
            }
        }

        return null;
    }

    private function searchForPriorityInConfigs(): ?int
    {
        /** @var array<string, int>|array<string> $config */
        $config = $this->isInstanceOf(DocumentationPage::class)
            ? Config::getArray('docs.sidebar.order', [])
            : Config::getArray('hyde.navigation.order', [
                'index' => 0,
                'posts' => 10,
                DocumentationPage::homeRouteName() => 100,
            ]);

        foreach ($this->configurationKeys as $key) {
            $priority = $this->parseNavigationPriorityConfig($config, $key);

            if ($priority !== null) {
                return $priority;
            }
        }

        return null;
    }

    /** @param  array<string, int>|array<string>  $config */
    private function parseNavigationPriorityConfig(array $config, string $pageKey): ?int
    {
        // Check if the config entry is a flat array or a keyed array.
        if (! array_key_exists($pageKey, $config)) {
            // Adding an offset makes so that pages with a front matter priority, or
            // explicit keyed priority selection that is lower can be shown first.
            // This is all to make it easier to mix ways of adding priorities.

            return $this->offset(
                array_flip($config)[$pageKey] ?? null,
                NavigationMenu::DEFAULT
            );
        }

        return $config[$pageKey] ?? null;
    }

    private function checkFilePrefixForOrder(): ?int
    {
        if (! $this->isInstanceOf(DocumentationPage::class)) {
            return null;
        }

        if (! NumericalPageOrderingHelper::hasNumericalPrefix($this->identifier)) {
            return null;
        }

        return NumericalPageOrderingHelper::splitNumericPrefix($this->identifier)[0];
    }

    private function canUseSubdirectoryForGroups(): bool
    {
        return $this->getSubdirectoryConfiguration() === 'dropdown'
            || $this->isInstanceOf(DocumentationPage::class);
    }

    private function pageIsInSubdirectory(): bool
    {
        return Str::contains($this->identifierWithinVersion(), '/');
    }

    private function getSubdirectoryName(): string
    {
        return Str::before($this->identifierWithinVersion(), '/');
    }

    private function identifierWithinVersion(): string
    {
        return $this->isInstanceOf(DocumentationPage::class)
            ? DocumentationVersions::stripVersionPrefix($this->identifier)
            : $this->identifier;
    }

    protected function getSubdirectoryConfiguration(): string
    {
        return Config::getString('hyde.navigation.subdirectory_display', 'hidden');
    }

    /** @param class-string<\Hyde\Pages\Concerns\HydePage> $class */
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
        /** @var string|null|int|bool $value */
        $value = $this->matter->get($key);

        return $value;
    }
}
