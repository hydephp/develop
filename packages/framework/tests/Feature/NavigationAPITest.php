<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Foundation\Facades\Routes;
use Hyde\Testing\MocksKernelFeatures;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\NavigationItem;

/**
 * High level tests for the Navigation API.
 */
class NavigationAPITest extends TestCase
{
    use MocksKernelFeatures;

    public function testNavigationMenus()
    {
        $this->withPages(['index', 'about', 'contact']);

        $menu = new NavigationMenu([
            new NavigationItem(Routes::get('index'), 'Home'),
            new NavigationItem(Routes::get('about'), 'About'),
            new NavigationItem(Routes::get('contact'), 'Contact'),
        ]);

        $this->assertSame([
            ['url' => 'index.html', 'title' => 'Home', 'active' => false],
            ['url' => 'about.html', 'title' => 'About', 'active' => false],
            ['url' => 'contact.html', 'title' => 'Contact', 'active' => false],
        ], $menu->toArray());
    }
}
