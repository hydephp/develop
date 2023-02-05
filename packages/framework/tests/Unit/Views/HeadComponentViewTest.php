<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Hyde;
use Hyde\Pages\VirtualPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @see resources/views/layouts/styles.blade.php
 */
class HeadComponentViewTest extends TestCase
{
    protected ?string $mockCurrentPage = null;

    protected function renderTestView(): string
    {
        $this->mockPage(new VirtualPage($this->mockCurrentPage ?? ''));

        return Blade::render(file_get_contents(
            Hyde::vendorPath('resources/views/layouts/head.blade.php')
        ));
    }

    public function test_component_can_be_rendered()
    {
        $this->assertStringContainsString('<meta charset="utf-8">', $this->renderTestView());
    }
}
