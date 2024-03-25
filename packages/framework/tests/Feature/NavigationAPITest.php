<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;
use Illuminate\Support\Arr;
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
            'Home' => 'index.html',
            'About' => 'about.html',
            'Contact' => 'contact.html',
        ], $this->toArray($menu));
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
            'Home' => 'index.html',
            'About' => [
                'items' => [
                    'About Us' => 'about.html',
                    'Contact Us' => 'contact.html',
                ],
            ],
        ], $this->toArray($menu));
    }

    protected function toArray(NavigationMenu $menu): array
    {
        return $menu->getItems()->mapWithKeys(function (NavigationItem|NavigationGroup $item): array {
            if ($item instanceof NavigationGroup) {
                return [
                    $item->getLabel() => [
                        'items' => Arr::mapWithKeys($item->getItems(), function (NavigationItem $item): array {
                            return [$item->getLabel() => $item->getLink()];
                        }),
                    ],
                ];
            }

            return [$item->getLabel() => $item->getLink()];
        })->all();
    }
}
