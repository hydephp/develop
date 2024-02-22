<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
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

    public function testComponentCanBeRendered()
    {
        $this->assertStringContainsString('id="main-navigation"', $this->render());
    }

    public function testComponentContainsDarkModeButton()
    {
        $this->assertStringContainsString('theme-toggle-button', $this->render());
    }

    public function testComponentContainsNavigationMenuToggleButton()
    {
        $this->assertStringContainsString('id="navigation-toggle-button"', $this->render());
    }

    public function testComponentContainsMainNavigationLinks()
    {
        $this->assertStringContainsString('id="main-navigation-links"', $this->render());
    }

    public function testComponentContainsIndexHtmlLink()
    {
        $this->assertStringContainsString('href="index.html"', $this->render());
    }

    public function testComponentNotContains404HtmlLink()
    {
        $this->assertStringNotContainsString('href="404.html"', $this->render());
    }

    public function testNavigationMenuLabelCanBeChangedInFrontMatter()
    {
        $this->file('_pages/foo.md', '---
navigation:
  label: "My custom label"
---
');
        Hyde::boot();

        $this->artisan('rebuild _pages/foo.md');
        $this->assertStringContainsString('My custom label', file_get_contents(Hyde::path('_site/foo.html')));
        Filesystem::unlink('_site/foo.html');
    }

    public function testNavigationMenuLabelCanBeChangedInBladeMatter()
    {
        $this->file('_pages/foo.blade.php', <<<'BLADE'
@extends('hyde::layouts.app')
@php($navigation = ['label' => 'My custom label'])
BLADE
        );
        Hyde::boot();

        $this->artisan('rebuild _pages/foo.blade.php');
        $this->assertStringContainsString('My custom label', file_get_contents(Hyde::path('_site/foo.html')));
        Filesystem::unlink('_site/foo.html');
    }
}
