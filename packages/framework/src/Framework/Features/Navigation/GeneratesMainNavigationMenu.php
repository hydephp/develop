<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;

use function collect;
use function strtolower;

/**
 * @experimental This class may change significantly before its release.
 *
 * @todo Consider making into a service which can create the sidebar as well.
 *
 * @see \Hyde\Framework\Features\Navigation\GeneratesDocumentationSidebarMenu
 */
class GeneratesMainNavigationMenu extends BaseMenuGenerator
{
    protected function generate(): void
    {
        parent::generate();

        collect(Config::getArray('hyde.navigation.custom', []))->each(function (NavItem $item): void {
            // Since these were added explicitly by the user, we can assume they should always be shown
            $this->items->push($item);
        });
    }

    protected function canAddRoute(Route $route): bool
    {
        return parent::canAddRoute($route)
            // While we for the most part can rely on the navigation visibility state provided by the navigation data factory,
            // we need to make an exception for documentation pages, which generally have a visible state, as the data is
            // also used in the sidebar. But we only want the documentation index page to be in the main navigation.
            && (! $route->getPage() instanceof DocumentationPage || $route->is(DocumentationPage::homeRouteName()));
    }

    protected function canGroupRoute(Route $route): bool
    {
        return parent::canGroupRoute($route) && $route->getPage()->navigationMenuGroup() !== null;
    }

    protected function createGroupItem(string $identifier, string $groupName): NavItem
    {
        $label = $this->searchForGroupLabelInConfig($identifier) ?? $groupName;
        $priority = $this->searchForDropdownPriorityInConfig($identifier);

        return NavItem::dropdown(static::normalizeGroupLabel($label), [], $priority);
    }

    protected function searchForGroupLabelInConfig(string $identifier): ?string
    {
        return Config::getArray('hyde.navigation.labels', [])[$identifier] ?? null;
    }

    /** Todo: Move into shared class */
    protected static function normalizeGroupLabel(string $label): string
    {
        // If there is no label, and the group is a slug, we can make a title from it
        if ($label === strtolower($label)) {
            return Hyde::makeTitle($label);
        }

        return $label;
    }

    /** Todo: Move into shared class */
    protected static function searchForDropdownPriorityInConfig(string $groupKey): ?int
    {
        return Config::getArray('hyde.navigation.order', [])[$groupKey] ?? null;
    }
}
