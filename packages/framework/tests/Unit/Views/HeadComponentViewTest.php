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
    protected function renderTestView(): string
    {
        return Blade::render($this->mockIncludes(file_get_contents(Hyde::vendorPath('resources/views/layouts/head.blade.php'))));
    }

    public function testComponentCanBeRendered()
    {
        $this->mockPage();
        $this->assertStringContainsString('<meta charset="utf-8">', $this->renderTestView());
    }

    public function testTitleElementUsesPageHtmlTitle()
    {
        $page = $this->createMock(VirtualPage::class);
        $page->method('htmlTitle')->willReturn('Foo Bar');
        $this->mockPage($page);

        $this->assertStringContainsString('<title>Foo Bar</title>', $this->renderTestView());
    }

    protected function mockIncludes(string $contents): string
    {
        return str_replace('@include', '#include', $contents);
    }
}
