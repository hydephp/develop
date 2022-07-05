<?php

namespace Hyde\Framework\Testing\Unit\Views;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\AssetService;
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

    public function test_component_renders_link_to_hyde_js_when_it_exists()
    {
        Hyde::touch(('_media/hyde.js'));
        $this->assertStringContainsString('<script defer src="media/hyde.js"', $this->renderTestView());
        unlink(Hyde::path('_media/hyde.js'));
    }

    public function test_component_does_not_render_link_to_hyde_js_when_it_does_not_exist()
    {
        $this->assertStringNotContainsString('<script defer src="media/hyde.js"', $this->renderTestView());
    }

    public function test_component_renders_cdn_link_when_no_local_file_exists()
    {
        $this->assertStringContainsString('https://cdn.jsdelivr.net/npm/hydefront', $this->renderTestView());
    }

    public function test_component_does_not_render_cdn_link_when_a_local_file_exists()
    {
        Hyde::touch(('_media/hyde.js'));
        $this->assertStringNotContainsString('https://cdn.jsdelivr.net/npm/hydefront', $this->renderTestView());
        unlink(Hyde::path('_media/hyde.js'));
    }

    public function test_cdn_link_uses_the_correct_version_defined_in_the_asset_manager()
    {
        $expectedVersion = (new AssetService)->version();
        $this->assertStringContainsString(
            'https://cdn.jsdelivr.net/npm/hydefront@'.$expectedVersion.'/dist/hyde.js',
            $this->renderTestView()
        );
    }
}
