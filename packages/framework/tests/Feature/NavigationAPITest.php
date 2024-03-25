<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Pages\BladePage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Arr;
use Hyde\Foundation\Facades\Routes;
use Hyde\Testing\MocksKernelFeatures;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationGroup;

/**
 * High level tests for the Navigation API to go along with the code-driven documentation.
 */
class NavigationAPITest extends TestCase
{
    use MocksKernelFeatures;

    public function testNavigationItems()
    {
        // Each navigation item is represented by a NavigationItem instance containing the needed information.

        // ## Creating Navigation Items

        // There are a few ways to create navigation items, depending on your needs.

        // You can create instances directly by passing either a URL or Route instance to the constructor.
        $item = new NavigationItem(Routes::get('index'), 'Home');
        $this->assertInstanceOf(NavigationItem::class, $item);
        $item = new NavigationItem('https://example.com', 'External Link');
        $this->assertInstanceOf(NavigationItem::class, $item);

        // You can also set the priority of the item, which determines its position in the menu.
        $item = new NavigationItem(Routes::get('index'), 'Home', 25);
        $this->assertSame(25, $item->getPriority());

        // You can also create instances using the static create method, which can automatically fill in the label and priority from a Route.
        $item = NavigationItem::create(Routes::get('index'));
        $this->assertSame('Home', $item->getLabel());
        $this->assertSame(0, $item->getPriority());

        // You can also pass a custom label and priority to the create method to override the defaults.
        $item = NavigationItem::create(Routes::get('index'), 'Custom Label', 50);
        $this->assertSame('Custom Label', $item->getLabel());
        $this->assertSame(50, $item->getPriority());

        // The create method also works with route keys.
        $item = NavigationItem::create('index');
        $this->assertSame('Home', $item->getLabel());
        $this->assertEquals(NavigationItem::create('index'), NavigationItem::create(Routes::get('index')));

        // You can also pass a URL to the create method.
        $item = NavigationItem::create('https://example.com');
        $this->assertSame('https://example.com', $item->getLink());

        // Unless you pass a custom label to URL items, the label will be the URL itself.
        $item = NavigationItem::create('https://example.com');
        $this->assertSame('https://example.com', $item->getLabel());

        // ## Navigation Item Methods

        // There are a few helper methods available on NavigationItem instances.
        $item = NavigationItem::create('index'); // Documentation ignores this line.

        // Get the label of the item.
        $this->assertSame('Home', $item->getLabel());

        // Get the link of the item.
        $this->assertSame('index.html', $item->getLink());

        // You can also get the link by casting the item to a string.
        $this->assertSame('index.html', (string) $item);

        // Get the priority of the item.
        $this->assertSame(0, $item->getPriority());

        // Get the underlying Page instance of the item, if it exists. If the item is not a routed page (like direct URLs), this will return null.
        $this->assertInstanceOf(BladePage::class, $item->getPage());

        // Check if the item is active (the current page being rendered).
        $this->assertFalse($item->isActive());
    }

    public function testNavigationMenus()
    {
        $this->withPages(['index', 'about', 'contact']);

        // Navigation menus are created with an array of NavigationItem instances.
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

        // You can get the items in the menu, which are automatically sorted by their priority.
        foreach ($menu->getItems() as $item) {
            $this->assertInstanceOf(NavigationItem::class, $item);

            $this->assertIsString($item->getLabel());
            $this->assertIsString($item->getLink());
        }
    }

    public function testNavigationMenusWithGroups()
    {
        $this->withPages(['index', 'about', 'contact']);

        // You can also add navigation groups to the menu, which can contain more items.
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
