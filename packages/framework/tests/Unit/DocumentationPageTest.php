<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Models\Pages\DocumentationPage
 * @covers \Hyde\Framework\Concerns\FrontMatter\Schemas\Constructors\ConstructsPageSchemas
 */
class DocumentationPageTest extends TestCase
{
    public function test_can_generate_table_of_contents()
    {
        $page = DocumentationPage::make(body: '# Foo');
        $this->assertIsString($page->getTableOfContents());
    }

    public function test_can_get_current_page_path()
    {
        $page = DocumentationPage::make('foo');
        $this->assertEquals('docs/foo', $page->getCurrentPagePath());

        config(['docs.output_directory' => 'documentation/latest/']);
        (new HydeServiceProvider($this->app))->register();
        $this->assertEquals('documentation/latest/foo', $page->getCurrentPagePath());
    }

    public function test_can_get_online_source_path()
    {
        $page = DocumentationPage::make('foo');
        $this->assertFalse($page->getOnlineSourcePath());
    }

    public function test_can_get_online_source_path_with_source_file_location_base()
    {
        config(['docs.source_file_location_base' => 'docs.example.com/edit']);
        $page = DocumentationPage::make('foo');
        $this->assertEquals('docs.example.com/edit/foo.md', $page->getOnlineSourcePath());
    }

    public function test_can_get_online_source_path_with_trailing_slash()
    {
        $page = DocumentationPage::make('foo');

        config(['docs.source_file_location_base' => 'edit/']);
        $this->assertEquals('edit/foo.md', $page->getOnlineSourcePath());

        config(['docs.source_file_location_base' => 'edit']);
        $this->assertEquals('edit/foo.md', $page->getOnlineSourcePath());
    }

    public function test_can_get_documentation_output_path()
    {
        $this->assertEquals('docs', DocumentationPage::getOutputDirectory());
    }

    public function test_can_get_documentation_output_path_with_custom_output_directory()
    {
        config(['docs.output_directory' => 'foo']);
        (new HydeServiceProvider($this->app))->register();
        $this->assertEquals('foo', DocumentationPage::getOutputDirectory());
    }

    public function test_can_get_documentation_output_path_with_trailing_slashes()
    {
        $tests = [
            'foo',
            'foo/',
            'foo//',
            'foo\\',
            '/foo/',
        ];

        foreach ($tests as $test) {
            config(['docs.output_directory' => $test]);
            (new HydeServiceProvider($this->app))->register();
            $this->assertEquals('foo', DocumentationPage::getOutputDirectory());
        }
    }

    public function test_get_source_path_returns_qualified_basename()
    {
        $this->assertEquals(
            DocumentationPage::qualifyBasename('foo'),
            (new DocumentationPage(identifier: 'foo'))->getSourcePath()
        );
    }

    public function test_get_source_path_returns_qualified_basename_for_nested_page()
    {
        $this->assertEquals(
            DocumentationPage::qualifyBasename('foo/bar'),
            (new DocumentationPage(identifier: 'foo/bar'))->getSourcePath()
        );
    }

    public function test_home_method_returns_null_when_there_is_no_index_page()
    {
        $this->assertNull(DocumentationPage::home());
    }

    public function test_home_method_returns_docs_index_route_when_it_exists()
    {
        Hyde::touch('_docs/index.md');
        $this->assertInstanceOf(Route::class, DocumentationPage::home());
        $this->assertEquals(Route::get('docs/index'), DocumentationPage::home());
        Hyde::unlink('_docs/index.md');
    }

    public function test_home_method_finds_docs_index_for_custom_output_directory()
    {
        config(['docs.output_directory' => 'foo']);
        (new HydeServiceProvider($this->app))->register();
        mkdir(Hyde::path('foo'));
        Hyde::touch('_docs/index.md');
        $this->assertInstanceOf(Route::class, DocumentationPage::home());
        $this->assertEquals(Route::get('foo/index'), DocumentationPage::home());
        Hyde::unlink('_docs/index.md');
        File::deleteDirectory(Hyde::path('foo'));
    }

    public function test_home_method_finds_docs_index_for_custom_nested_output_directory()
    {
        config(['docs.output_directory' => 'foo/bar']);
        (new HydeServiceProvider($this->app))->register();
        mkdir(Hyde::path('foo'));
        mkdir(Hyde::path('foo/bar'));
        Hyde::touch('_docs/index.md');
        $this->assertInstanceOf(Route::class, DocumentationPage::home());
        $this->assertEquals(Route::get('foo/bar/index'), DocumentationPage::home());
        Hyde::unlink('_docs/index.md');
        File::deleteDirectory(Hyde::path('foo'));
    }

    public function test_has_table_of_contents()
    {
        $this->assertIsBool(DocumentationPage::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', true);
        $this->assertTrue(DocumentationPage::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', false);
        $this->assertFalse(DocumentationPage::hasTableOfContents());
    }

    public function test_compiled_pages_originating_in_subdirectories_get_output_to_root_docs_path()
    {
        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('docs/bar.html', $page->getOutputPath());
    }
}
