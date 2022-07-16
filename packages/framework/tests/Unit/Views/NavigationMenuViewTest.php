<?php

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Testing\TestCase;

/**
 * @see resources/views/layouts/navigation.blade.php
 */
class NavigationMenuViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRoute();
        $this->mockPage();
    }

    protected function render(): string
    {
        return view('hyde::layouts.navigation')->render();
    }

    public function test_component_can_be_rendered()
    {
        $this->assertStringContainsString('id="main-navigation"', $this->render());
    }

    public function test_component_contains_dark_mode_button()
    {
        $this->assertStringContainsString('theme-toggle-button', $this->render());
    }

    public function test_component_contains_navigation_menu_toggle_button()
    {
        $this->assertStringContainsString('id="navigation-toggle-button"', $this->render());
    }

    public function test_component_contains_main_navigation_links()
    {
        $this->assertStringContainsString('id="main-navigation-links"', $this->render());
    }

    public function test_component_contains_index_html_link()
    {
        $this->assertStringContainsString('href="index.html"', $this->render());
    }

    public function test_component_not_contains_404_html_link()
    {
        $this->assertStringNotContainsString('href="404.html"', $this->render());
    }
}
