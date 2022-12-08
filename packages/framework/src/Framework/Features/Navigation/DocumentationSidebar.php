<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Foundation\Facades\Router;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\Route;
use function tap;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\DocumentationSidebarTest
 */
class DocumentationSidebar extends BaseNavigationMenu
{
    /** @return $this */
    public function generate(): static
    {
        Router::getRoutes(DocumentationPage::class)->each(function (Route $route): void {
            // TODO investigate if this is still needed
            $this->items->push(tap(NavItem::fromRoute($route)->setPriority($this->getPriorityForRoute($route)), function (NavItem $item): void {
                $item->label = $item->route->getPage()->data('navigation.label');
            }));
        });

        return $this;
    }

    public function hasGroups(): bool
    {
        return parent::hasGroups() && ($this->getGroups() !== ['other']);
    }

    protected function getPriorityForRoute(Route $route): int
    {
        return $route->getPage()->data('navigation.priority');
    }

    protected function filterDocumentationPage(NavItem $item): bool
    {
        return ! parent::filterDocumentationPage($item);
    }
}
