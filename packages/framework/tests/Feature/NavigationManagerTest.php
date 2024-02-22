<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Framework\Features\Navigation\NavigationManager;
use Hyde\Framework\Features\Navigation\MainNavigationMenu;
use Hyde\Framework\Features\Navigation\DocumentationSidebar;
use Hyde\Testing\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\MainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 * @covers \Hyde\Framework\Features\Navigation\NavigationManager
 */
class NavigationManagerTest extends TestCase
{
    public function testCanRegisterMenu()
    {
        $manager = new NavigationManager();

        $menu = $this->createMock(MainNavigationMenu::class);
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

        $menu = $this->createMock(MainNavigationMenu::class);
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

    public function testCannotGetContainerMenusBeforeKernelIsBooted()
    {
        $this->expectException(BindingResolutionException::class);

        $this->assertNull(app('navigation.main'));
        $this->assertNull(app('navigation.sidebar'));
    }

    public function testCanGetMainNavigationMenuFromContainer()
    {
        Hyde::boot();

        $this->assertInstanceOf(MainNavigationMenu::class, app('navigation.main'));
    }

    public function testCanGetDocumentationSidebarFromContainer()
    {
        Hyde::boot();

        $this->assertInstanceOf(DocumentationSidebar::class, app('navigation.sidebar'));
    }

    public function testCanGetMainNavigationMenuFromContainerUsingShorthand()
    {
        Hyde::boot();

        $this->assertSame(MainNavigationMenu::get(), app('navigation.main'));
    }

    public function testCanGetDocumentationSidebarFromContainerUsingShorthand()
    {
        Hyde::boot();

        $this->assertSame(DocumentationSidebar::get(), app('navigation.sidebar'));
    }
}
