<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Hyde;
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

    public function test_navigation_menu_label_can_be_changed_in_front_matter()
    {
        $this->file('_pages/foo.md', '---
navigation: 
  label: "My custom label"
---
');
        Hyde::boot();

        $this->artisan('rebuild _pages/foo.md');
        $this->assertStringContainsString('My custom label', file_get_contents(Hyde::path('_site/foo.html')));
        Hyde::unlink('_site/foo.html');
    }

    public function test_navigation_menu_label_can_be_changed_in_blade_matter()
    {
        $this->file('_pages/foo.blade.php', <<<'BLADE'
@extends('hyde::layouts.app')
@php($navigation = ['label' => 'My custom label'])
BLADE
);
        Hyde::boot();

        $this->artisan('rebuild _pages/foo.blade.php');
        $this->assertStringContainsString('My custom label', file_get_contents(Hyde::path('_site/foo.html')));
        Hyde::unlink('_site/foo.html');
    }
}
