<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\PageRoute;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Pages\DocumentationPage
 * @covers \Hyde\Framework\Factories\Concerns\HasFactory
 * @covers \Hyde\Framework\Factories\NavigationDataFactory
 */
class DocumentationPageTest extends TestCase
{
    public function testCanGenerateTableOfContents()
    {
        $page = DocumentationPage::make(markdown: '# Foo');
        $this->assertIsString($page->getTableOfContents());
    }

    public function testCanGetCurrentPagePath()
    {
        $page = DocumentationPage::make('foo');
        $this->assertEquals('docs/foo', $page->getRouteKey());
    }

    public function testCanGetCurrentCustomPagePath()
    {
        config(['hyde.output_directories.documentation-page' => 'documentation/latest/']);
        (new HydeServiceProvider($this->app))->register();

        $page = DocumentationPage::make('foo');
        $this->assertEquals('documentation/latest/foo', $page->getRouteKey());
    }

    public function testCanGetCurrentPagePathWhenUsingFlattenedOutputPaths()
    {
        Config::set('docs.flattened_output_paths', true);

        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('docs/bar', $page->getRouteKey());

        config(['hyde.output_directories.documentation-page' => 'documentation/latest/']);
        (new HydeServiceProvider($this->app))->register();

        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('documentation/latest/bar', $page->getRouteKey());
    }

    public function testCanGetCurrentPagePathWhenNotUsingFlattenedOutputPaths()
    {
        Config::set('docs.flattened_output_paths', false);

        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('docs/foo/bar', $page->getRouteKey());

        config(['hyde.output_directories.documentation-page' => 'documentation/latest/']);
        (new HydeServiceProvider($this->app))->register();

        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('documentation/latest/foo/bar', $page->getRouteKey());
    }

    public function testCanGetOnlineSourcePath()
    {
        $page = DocumentationPage::make('foo');
        $this->assertFalse($page->getOnlineSourcePath());
    }

    public function testCanGetOnlineSourcePathWithSourceFileLocationBase()
    {
        config(['docs.source_file_location_base' => 'docs.example.com/edit']);
        $page = DocumentationPage::make('foo');
        $this->assertEquals('docs.example.com/edit/foo.md', $page->getOnlineSourcePath());
    }

    public function testCanGetOnlineSourcePathWithTrailingSlash()
    {
        $page = DocumentationPage::make('foo');

        config(['docs.source_file_location_base' => 'edit/']);
        $this->assertEquals('edit/foo.md', $page->getOnlineSourcePath());

        config(['docs.source_file_location_base' => 'edit']);
        $this->assertEquals('edit/foo.md', $page->getOnlineSourcePath());
    }

    public function testCanGetDocumentationOutputPath()
    {
        $this->assertEquals('docs', DocumentationPage::outputDirectory());
    }

    public function testCanGetDocumentationOutputPathWithCustomOutputDirectory()
    {
        config(['hyde.output_directories.documentation-page' => 'foo']);
        (new HydeServiceProvider($this->app))->register();
        $this->assertEquals('foo', DocumentationPage::outputDirectory());
    }

    public function testCanGetDocumentationOutputPathWithTrailingSlashes()
    {
        $tests = [
            'foo',
            'foo/',
            'foo//',
            'foo\\',
            '/foo/',
        ];

        foreach ($tests as $test) {
            config(['hyde.output_directories.documentation-page' => $test]);
            (new HydeServiceProvider($this->app))->register();
            $this->assertEquals('foo', DocumentationPage::outputDirectory());
        }
    }

    public function testGetSourcePathReturnsQualifiedBasename()
    {
        $this->assertEquals(
            DocumentationPage::sourcePath('foo'),
            (new DocumentationPage(identifier: 'foo'))->getSourcePath()
        );
    }

    public function testGetSourcePathReturnsQualifiedBasenameForNestedPage()
    {
        $this->assertEquals(
            DocumentationPage::sourcePath('foo/bar'),
            (new DocumentationPage(identifier: 'foo/bar'))->getSourcePath()
        );
    }

    public function testHomeMethodReturnsNullWhenThereIsNoIndexPage()
    {
        $this->assertNull(DocumentationPage::home());
    }

    public function testHomeMethodReturnsDocsIndexRouteWhenItExists()
    {
        Filesystem::touch('_docs/index.md');
        $this->assertInstanceOf(PageRoute::class, DocumentationPage::home());
        $this->assertEquals(Routes::get('docs/index'), DocumentationPage::home());
        Filesystem::unlink('_docs/index.md');
    }

    public function testHomeMethodFindsDocsIndexForCustomOutputDirectory()
    {
        config(['hyde.output_directories.documentation-page' => 'foo']);
        (new HydeServiceProvider($this->app))->register();
        mkdir(Hyde::path('foo'));
        Filesystem::touch('_docs/index.md');
        $this->assertInstanceOf(PageRoute::class, DocumentationPage::home());
        $this->assertEquals(Routes::get('foo/index'), DocumentationPage::home());
        Filesystem::unlink('_docs/index.md');
        File::deleteDirectory(Hyde::path('foo'));
    }

    public function testHomeMethodFindsDocsIndexForCustomNestedOutputDirectory()
    {
        config(['hyde.output_directories.documentation-page' => 'foo/bar']);
        (new HydeServiceProvider($this->app))->register();
        mkdir(Hyde::path('foo'));
        mkdir(Hyde::path('foo/bar'));
        Filesystem::touch('_docs/index.md');
        $this->assertInstanceOf(PageRoute::class, DocumentationPage::home());
        $this->assertEquals(Routes::get('foo/bar/index'), DocumentationPage::home());
        Filesystem::unlink('_docs/index.md');
        File::deleteDirectory(Hyde::path('foo'));
    }

    public function testHomeRouteNameMethodReturnsOutputDirectorySlashIndex()
    {
        $this->assertSame('docs/index', DocumentationPage::homeRouteName());
    }

    public function testHomeRouteNameMethodReturnsCustomizedOutputDirectorySlashIndex()
    {
        config(['hyde.output_directories.documentation-page' => 'foo/bar']);
        (new HydeServiceProvider($this->app))->register();

        $this->assertSame('foo/bar/index', DocumentationPage::homeRouteName());
    }

    public function testHasTableOfContents()
    {
        $this->assertIsBool(DocumentationPage::hasTableOfContents());

        Config::set('docs.sidebar.table_of_contents.enabled', true);
        $this->assertTrue(DocumentationPage::hasTableOfContents());

        Config::set('docs.sidebar.table_of_contents.enabled', false);
        $this->assertFalse(DocumentationPage::hasTableOfContents());
    }

    public function testCompiledPagesOriginatingInSubdirectoriesGetOutputToRootDocsPath()
    {
        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('docs/bar.html', $page->getOutputPath());
    }

    public function testCompiledPagesOriginatingInSubdirectoriesGetOutputToRootDocsPathWhenUsingFlattenedOutputPaths()
    {
        Config::set('docs.flattened_output_paths', true);
        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('docs/bar.html', $page->getOutputPath());
    }

    public function testCompiledPagesOriginatingInSubdirectoriesRetainSubdirectoryStructureWhenNotUsingFlattenedOutputPaths()
    {
        Config::set('docs.flattened_output_paths', false);
        $page = DocumentationPage::make('foo/bar');
        $this->assertEquals('docs/foo/bar.html', $page->getOutputPath());
    }

    public function testPageHasFrontMatter()
    {
        $this->markdown('_docs/foo.md', matter: $expected = [
            'foo' => 'bar',
            'bar' => [
                'baz' => 'qux',
            ],
        ]);
        $page = DocumentationPage::parse('foo');
        $this->assertNotNull($page->matter());
        $this->assertNotEmpty($page->matter());
        $this->assertEquals(new FrontMatter($expected), $page->matter());
    }

    public function testPageCanBeHiddenFromSidebarUsingFrontMatter()
    {
        $this->markdown('_docs/foo.md', matter: [
            'navigation' => [
                'hidden' => true,
            ],
        ]);
        $page = DocumentationPage::parse('foo');
        $this->assertFalse($page->showInNavigation());
    }

    public function testPageIsVisibleInSidebarWithoutUsingFrontMatter()
    {
        $this->markdown('_docs/foo.md');
        $page = DocumentationPage::parse('foo');
        $this->assertTrue($page->showInNavigation());
    }

    public function testPageCanSetSidebarPriorityUsingFrontMatter()
    {
        $this->file('_docs/foo.md', '---
navigation:
    priority: 10
---
');
        $page = DocumentationPage::parse('foo');
        $this->assertEquals(10, $page->navigationMenuPriority());
    }

    public function testPageCanSetSidebarLabelUsingFrontMatter()
    {
        $this->file('_docs/foo.md', '---
navigation:
    label: Bar
---
');
        $page = DocumentationPage::parse('foo');
        $this->assertEquals('Bar', $page->navigationMenuLabel());
    }
}
