<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Navigation\NavigationManager;
use Hyde\Framework\Features\Navigation\NavigationMenu;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationManager
 */
class NavigationManagerTest extends TestCase
{
    public function testCanRegisterMenu()
    {
        $manager = new NavigationManager();

        $menu = $this->createMock(NavigationMenu::class);
        $manager->registerMenu('foo', $menu);

        $reflection = new \ReflectionClass($manager);
        $property = $reflection->getProperty('menus');

        $menus = $property->getValue($manager);

        $this->assertArrayHasKey('foo', $menus);
        $this->assertSame($menu, $menus['foo']);
    }

    public function testCanGetMenu()
    {
        $manager = new NavigationManager();

        $menu = $this->createMock(NavigationMenu::class);
        $manager->registerMenu('foo', $menu);

        $retrievedMenu = $manager->getMenu('foo');

        $this->assertSame($menu, $retrievedMenu);
    }

    public function testGetMenuThrowsExceptionForNonExistentMenu()
    {
        $manager = new NavigationManager();

        $this->expectException(\Exception::class);
        $manager->getMenu('foo');
    }
}
