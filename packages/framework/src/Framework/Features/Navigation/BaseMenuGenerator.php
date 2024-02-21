<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;

/**
 * @experimental This class may change significantly before its release.
 */
abstract class BaseMenuGenerator
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Navigation\NavItem> */
    protected Collection $items;

    /** @var \Hyde\Foundation\Kernel\RouteCollection<string, \Hyde\Support\Models\Route> */
    protected RouteCollection $routes;

    protected function __construct()
    {
        $this->items = new Collection();

        if ($this instanceof GeneratesDocumentationSidebarMenu) {
            $this->routes = Routes::getRoutes(DocumentationPage::class);
        } else {
            $this->routes = Routes::all();
        }
    }
}
