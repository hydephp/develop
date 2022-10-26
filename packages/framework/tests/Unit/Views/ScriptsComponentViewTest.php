<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @see resources/views/layouts/scripts.blade.php
 */
class ScriptsComponentViewTest extends TestCase
{
    protected ?string $mockCurrentPage = null;

    protected function renderTestView(): string
    {
        config(['hyde.cache_busting' => false]);
        view()->share('currentPage', $this->mockCurrentPage ?? '');

        return Blade::render(file_get_contents(
            Hyde::vendorPath('resources/views/layouts/scripts.blade.php')
        ));
    }

    public function test_component_can_be_rendered()
    {
        $this->assertStringContainsString('<script defer', $this->renderTestView());
    }

    public function test_component_has_link_to_app_js_file_when_it_exists()
    {
        Hyde::touch(('_media/app.js'));
        $this->assertStringContainsString('<script defer src="media/app.js"', $this->renderTestView());
        unlink(Hyde::path('_media/app.js'));
    }

    public function test_component_does_not_render_link_to_app_js_when_it_does_not_exist()
    {
        $this->assertStringNotContainsString('<script defer src="media/app.js"', $this->renderTestView());
    }

    public function test_component_uses_relative_path_to_app_js_file_for_nested_pages()
    {
        Hyde::touch(('_media/app.js'));
        $this->mockCurrentPage = 'foo';
        $this->assertStringContainsString('<script defer src="media/app.js"', $this->renderTestView());
        $this->mockCurrentPage = 'foo/bar';
        $this->assertStringContainsString('<script defer src="../media/app.js"', $this->renderTestView());
        $this->mockCurrentPage = 'foo/bar/cat.html';
        $this->assertStringContainsString('<script defer src="../../media/app.js"', $this->renderTestView());
        $this->mockCurrentPage = null;
        unlink(Hyde::path('_media/app.js'));
    }

    public function test_scripts_can_be_pushed_to_the_component_scripts_stack()
    {
        view()->share('currentPage', '');

        $this->assertStringContainsString('foo bar',
             Blade::render('
                @push("scripts")
                foo bar
                @endpush
                
                @include("hyde::layouts.scripts")'
             )
        );
    }
}
