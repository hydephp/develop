<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Helpers\Asset;
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
        config(['hyde.cache_busting' => false]);
        view()->share('currentPage', $this->mockCurrentPage ?? '');

        return Blade::render(file_get_contents(
            Hyde::vendorPath('resources/views/layouts/styles.blade.php')
        ));
    }

    public function test_component_can_be_rendered()
    {
        $this->assertStringContainsString('<link rel="stylesheet"', $this->renderTestView());
    }

    public function test_component_has_link_to_app_css_file()
    {
        $this->assertStringContainsString('<link rel="stylesheet" href="media/app.css"', $this->renderTestView());
    }

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

    public function test_component_does_not_render_link_to_app_css_when_it_does_not_exist()
    {
        rename(Hyde::path('_media/app.css'), Hyde::path('_media/app.css.bak'));
        $this->assertStringNotContainsString('<link rel="stylesheet" href="media/app.css"', $this->renderTestView());
        rename(Hyde::path('_media/app.css.bak'), Hyde::path('_media/app.css'));
    }

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

    public function test_component_renders_app_cdn_link_when_enabled_in_config()
    {
        config(['hyde.load_app_styles_from_cdn' => true]);
        $this->assertStringContainsString(Asset::cdnLink('app.css'), $this->renderTestView());
    }

    public function test_component_does_not_render_link_to_local_app_css_when_cdn_link_is_enabled_in_config()
    {
        config(['hyde.load_app_styles_from_cdn' => true]);
        $this->assertStringNotContainsString('<link rel="stylesheet" href="media/app.css"', $this->renderTestView());
    }

    public function test_component_does_not_render_cdn_link_when_a_local_file_exists()
    {
        Hyde::touch(('_media/hyde.css'));
        $this->assertStringNotContainsString('https://cdn.jsdelivr.net/npm/hydefront', $this->renderTestView());
        unlink(Hyde::path('_media/hyde.css'));
    }
}
