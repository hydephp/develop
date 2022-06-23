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
	protected ?string $mockCurrentPage = null;

    protected function renderTestView(): string
    {
        view()->share('currentPage', $this->mockCurrentPage ?? '');

        return Blade::render(file_get_contents(
            Hyde::vendorPath('resources/views/layouts/styles.blade.php')
        ));
    }

    // component can be rendered
    public function test_component_can_be_rendered()
    {
        $this->assertStringContainsString('<link rel="stylesheet"', $this->renderTestView());
    }

    // component has link to the app.css file
    public function test_component_has_link_to_app_css_file()
    {
        $this->assertStringContainsString('<link rel="stylesheet" href="media/app.css"', $this->renderTestView());
    }

    // component uses relative path to app.css file for nested pages
    public function test_component_uses_relative_path_to_app_css_file_for_nested_pages()
    {
        $this->mockCurrentPage = 'foo';
        $this->assertStringContainsString('<link rel="stylesheet" href="media/app.css"', $this->renderTestView());
        $this->mockCurrentPage = 'foo/bar';
        $this->assertStringContainsString('<link rel="stylesheet" href="../media/app.css"', $this->renderTestView());
        $this->mockCurrentPage = 'foo/bar/cat.html';
        $this->assertStringContainsString('<link rel="stylesheet" href="../../media/app.css"', $this->renderTestView());
        $this->mockCurrentPage = null;
    }

    // component does not render link to app.css when it does not exist
    public function test_component_does_not_render_link_to_app_css_when_it_does_not_exist()
    {
        rename(Hyde::path('_media/app.css'), Hyde::path('_media/app.css.bak'));
        $this->assertStringNotContainsString('<link rel="stylesheet" href="media/app.css"', $this->renderTestView());
        rename(Hyde::path('_media/app.css.bak'), Hyde::path('_media/app.css'));
    }

    // Test styles can be pushed to the component's styles stack
    public function test_styles_can_be_pushed_to_the_component_styles_stack()
    {
        view()->share('currentPage', '');

        $this->assertStringContainsString('foo bar',
             Blade::render('
                @push("styles")
                foo bar
                @endpush
                
                @include("hyde::layouts.styles")'
             )
        );
    }
}
