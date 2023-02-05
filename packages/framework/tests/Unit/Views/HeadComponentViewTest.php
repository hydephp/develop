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

        return Blade::render($this->mockIncludes(file_get_contents(Hyde::vendorPath('resources/views/layouts/head.blade.php'))));
    }

    public function testComponentCanBeRendered()
    {
        $this->assertStringContainsString('<meta charset="utf-8">', $this->renderTestView());
    }

    public function testTitleElementUsesPageHtmlTitle()
    {
        $this->mockPage = $this->createMock(VirtualPage::class);
        $this->mockPage->method('htmlTitle')->willReturn('Foo Bar');

        $this->assertStringContainsString('<title>Foo Bar</title>', $this->renderTestView());
    }

    protected function mockIncludes(string $contents): string
    {
        return str_replace('@include', '#include', $contents);
    }
}
