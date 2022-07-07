<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Modules\Routing\Route;
use Hyde\Testing\TestCase;
use Hyde\Framework\Modules\Navigation\NavigationMenu;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Modules\Navigation\NavigationMenu
 */
class NavigationMenuTest extends TestCase
{
    public function test_constructor()
    {
        $menu = new NavigationMenu();

        $this->assertInstanceOf(NavigationMenu::class, $menu);
        $this->assertInstanceOf(Collection::class, $menu);
    }

    public function test_home_route()
    {
        $menu = new NavigationMenu();

        $this->assertInstanceOf(Route::class, $menu->homeRoute);
        $this->assertEquals('index', $menu->homeRoute->getRouteKey());
    }

    public function test_set_current_route()
    {
        $menu = new NavigationMenu();

        $this->assertFalse(isset($menu->currentRoute));
        $menu->setCurrentRoute(Route::get('index'));
        $this->assertTrue(isset($menu->currentRoute));
        $this->assertInstanceOf(Route::class, $menu->currentRoute);
        $this->assertEquals('index', $menu->currentRoute->getRouteKey());
    }

    public function test_generate()
    {
        $menu = new NavigationMenu();

        $menu->generate();
        $this->assertInstanceOf(Collection::class, $menu);
        $this->assertGreaterThan(0, $menu->count());
        $this->assertInstanceOf(Route::class, $menu->first());
        $this->assertEquals('index', $menu->first()->getRouteKey());
    }

    public function test_static_create()
    {
        $menu = NavigationMenu::create(Route::get('index'));
        $this->assertInstanceOf(NavigationMenu::class, $menu);
        $this->assertEquals(
            (new NavigationMenu())->setCurrentRoute(Route::get('index'))->generate(),
            NavigationMenu::create(Route::get('index'))
        );
    }
}
