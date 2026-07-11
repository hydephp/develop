<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Facades\Render;
use Illuminate\Contracts\Support\Arrayable;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

use function app;
use function is_string;

class DocumentationSidebar extends NavigationMenu
{
    public readonly ?DocumentationVersion $version;

    /**
     * Get the navigation menu instance from the service container.
     *
     * When documentation versioning is enabled, this resolves the sidebar
     * for the version of the page currently being rendered.
     */
    public static function get(): static
    {
        $version = DocumentationVersions::current();

        return app($version === null ? 'navigation.sidebar' : "navigation.sidebar.$version->name");
    }

    public function __construct(Arrayable|array $items = [], ?DocumentationVersion $version = null)
    {
        parent::__construct($items);

        $this->version = $version;
    }

    /**
     * Get the route for this sidebar's documentation index page, if it exists.
     */
    public function getHomeRoute(): ?Route
    {
        return $this->version?->home() ?? DocumentationPage::home();
    }

    public function getHeader(): string
    {
        return Config::getString('docs.sidebar.header', 'Documentation');
    }

    public function getFooter(): ?string
    {
        /** @var null|string|false $option */
        $option = Config::get('docs.sidebar.footer', '[Back to home page](../)');

        if (is_string($option)) {
            return $option;
        }

        return null;
    }

    public function hasFooter(): bool
    {
        return $this->getFooter() !== null;
    }

    public function isCollapsible(): bool
    {
        return Config::getBool('docs.sidebar.collapsible', true);
    }

    public function hasGroups(): bool
    {
        return $this->getItems()->contains(fn (NavigationItem|NavigationGroup $item): bool => $item instanceof NavigationGroup);
    }

    /**
     * Get the group that should be open when the sidebar is loaded.
     *
     * @internal This method offloads logic for the sidebar view, and is not intended to be used in other contexts.
     */
    public function getActiveGroup(): ?NavigationGroup
    {
        if ($this->items->isEmpty() || (! $this->hasGroups()) || (! $this->isCollapsible()) || Render::getPage() === null) {
            return null;
        }

        $currentPage = Render::getPage();

        if ($currentPage->getRoute()->is($this->version?->homeRouteName() ?? DocumentationPage::homeRouteName()) && blank($currentPage->navigationMenuGroup())) {
            // Unless the index page has a specific group set, the first group in the sidebar should be open when visiting the index page.
            return $this->items->sortBy(fn (NavigationGroup $item): int => $item->getPriority())->first();
        }

        /** @var ?NavigationGroup $first */
        $first = $this->items->first(function (NavigationGroup $group) use ($currentPage): bool {
            // A group is active when it contains the current page being rendered.
            return $currentPage->navigationMenuGroup() && $group->getGroupKey() === NavigationGroup::normalizeGroupKey($currentPage->navigationMenuGroup());
        });

        return $first;
    }
}
