<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\VirtualPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @see resources/views/layouts/styles.blade.php
 */
class HeadComponentViewTest extends TestCase
{
    protected ?HydePage $mockPage = null;

    protected function renderTestView(): string
    {
        $this->mockPage($this->mockPage ?? new VirtualPage('foo'));
        $this->mockPage = null;

        $contents = file_get_contents(Hyde::vendorPath('resources/views/layouts/head.blade.php'));
        return Blade::render($contents);
    }

    public function testComponentCanBeRendered()
    {
        $this->assertStringContainsString('<meta charset="utf-8">', $this->renderTestView());
    }

    public function testTitleElementUsesPageHtmlTitle()
    {
        config(['site.name' => 'Site Name']);
        $page = $this->createMock(VirtualPage::class);
        $page->method('htmlTitle')->willReturn('Foo Bar');
        $this->mockPage = $page;
        $this->assertStringContainsString('<title>Site Name - Foo Bar</title>', $this->renderTestView());
    }
}
