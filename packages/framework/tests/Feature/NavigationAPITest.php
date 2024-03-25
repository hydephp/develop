<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Hyde\Foundation\Facades\Routes;
use Hyde\Testing\MocksKernelFeatures;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationGroup;

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
            ['index.html' => 'Home'],
            ['about.html' => 'About'],
            ['contact.html' => 'Contact'],
        ], $menu->toArray());
    }

    public function testNavigationMenusWithGroups()
    {
        $this->withPages(['index', 'about', 'contact']);

        $menu = new NavigationMenu([
            new NavigationItem(Routes::get('index'), 'Home'),
            new NavigationGroup('About', [
                new NavigationItem(Routes::get('about'), 'About Us'),
                new NavigationItem(Routes::get('contact'), 'Contact Us'),
            ]),
        ]);

        $this->assertSame([
            ['index.html' => 'Home'],
            ['label' => 'About', 'items' => [
                ['about.html' => 'About Us'],
                ['contact.html' => 'Contact Us'],
            ]],
        ], $menu->toArray());
    }
}
