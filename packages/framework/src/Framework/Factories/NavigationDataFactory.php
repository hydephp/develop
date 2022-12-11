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
        return $this->matter('navigation.label')
            ?? $this->searchForLabelInConfig()
            ?? $this->matter('title')
            ?? $this->title;
    }

    protected function makeGroup(): ?string
    {
        // If the documentation page is in a subdirectory,
        if ((Str::contains($this->identifier, '/') && ($this->getSubdirectoryConfiguration() === 'dropdown')) || $this->isInstanceOf(DocumentationPage::class)) {
            // then we can use that as the category/group name.
            return Str::before($this->identifier, '/');
        }

        // Otherwise, we look in the front matter.
        return $this->searchForGroupInFrontMatter();
    }

    protected function makeHidden(): ?bool
    {
        if ($this->isInstanceOf(MarkdownPost::class)) {
            return true;
        }

        if ($this->matter('navigation.hidden', false)) {
            return true;
        }

        if (in_array($this->routeKey, config('hyde.navigation.exclude', ['404']))) {
            return true;
        }

        if (Str::contains($this->identifier, '/') && $this->getSubdirectoryConfiguration() === 'hidden') {
            return true;
        }

        return false;
    }

    protected function makePriority(): ?int
    {
        if ($this->matter('navigation.priority') !== null) {
            return $this->matter('navigation.priority');
        }

        return $this->isInstanceOf(DocumentationPage::class)
            ? $this->findPriorityInSidebarConfig(array_flip(config('docs.sidebar_order', []))) ?? self::FALLBACK_PRIORITY
            : $this->findPriorityInNavigationConfig(config('hyde.navigation.order', [])) ?? self::FALLBACK_PRIORITY;
    }

    private function findPriorityInNavigationConfig(array $config): ?int
    {
        return array_key_exists($this->routeKey, $config) ? (int) $config[$this->routeKey] : null;
    }

    private function findPriorityInSidebarConfig(array $config): ?int
    {
        // Sidebars uses a special syntax where the keys are just the page identifiers in a flat array

        // Adding 250 makes so that pages with a front matter priority that is lower can be shown first.
        // It's lower than the fallback of 500 so that the config ones still come first.
        // This is all to make it easier to mix ways of adding priorities.

        return isset($config[$this->identifier])
            ? $config[$this->identifier] + (self::CONFIG_OFFSET)
            : null;
    }

    protected function searchForLabelInConfig(): ?string
    {
        $labelConfig = array_merge([
            'index' => 'Home',
            'docs/index' => 'Docs',
        ], config('hyde.navigation.labels', []));

        if (isset($labelConfig[$this->routeKey])) {
            return $labelConfig[$this->routeKey];
        }

        return null;
    }

    protected function isInstanceOf(string $class): bool
    {
        return is_a($this->pageClass, $class, true);
    }

    protected function searchForGroupInFrontMatter(): ?string
    {
        return $this->matter('navigation.group')
            ?? $this->matter('navigation.category')
            ?? $this->defaultGroup();
    }

    private function defaultGroup(): ?string
    {
        if ($this->isInstanceOf(DocumentationPage::class)) {
            return 'other';
        }

        return null;
    }

    protected static function getSubdirectoryConfiguration(): string
    {
        return config('hyde.navigation.subdirectories', 'hidden');
    }
}
