<?php

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @see resources/views/layouts/styles.blade.php
 */
class StylesComponentViewTest extends TestCase
{
    protected function renderTestView(): string
    {
        view()->share('currentPage', 'foo');

        return Blade::render(file_get_contents(
            Hyde::vendorPath('resources/views/layouts/styles.blade.php')
        ));
    }

    public function test_component_can_be_rendered()
    {
        $this->assertStringContainsString('<link rel="stylesheet"', $this->renderTestView());
    }
}
