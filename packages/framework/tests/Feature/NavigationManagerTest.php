<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Navigation\NavigationManager;
use Hyde\Framework\Features\Navigation\BaseNavigationMenu;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Navigation\NavigationManager
 */
class NavigationManagerTest extends TestCase
{
    protected NavigationManager $navigationManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->navigationManager = new NavigationManager();
    }

    /** @author Copilot */
    public function testRegisterMenu()
    {
        $menu = $this->createMock(BaseNavigationMenu::class);
        $this->navigationManager->registerMenu('test', $menu);

        $reflection = new \ReflectionClass($this->navigationManager);
        $property = $reflection->getProperty('menus');
        $property->setAccessible(true);

        $menus = $property->getValue($this->navigationManager);

        $this->assertArrayHasKey('test', $menus);
        $this->assertSame($menu, $menus['test']);
    }

    /** @author Copilot */
    public function testGetMenu()
    {
        $menu = $this->createMock(BaseNavigationMenu::class);
        $this->navigationManager->registerMenu('test', $menu);

        $retrievedMenu = $this->navigationManager->getMenu('test');

        $this->assertSame($menu, $retrievedMenu);
    }

    /** @author Copilot */
    public function testGetMenuThrowsExceptionForNonExistentMenu()
    {
        $this->expectException(\Exception::class);
        $this->navigationManager->getMenu('nonexistent');
    }
}
