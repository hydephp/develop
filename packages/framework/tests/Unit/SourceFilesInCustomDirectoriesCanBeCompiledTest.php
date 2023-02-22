<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * Class SourceFilesInCustomDirectoriesCanBeCompiledTest.
 */
class SourceFilesInCustomDirectoriesCanBeCompiledTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        is_dir(Hyde::path('testSourceDir')) || mkdir(Hyde::path('testSourceDir'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(Hyde::path('testSourceDir'));

        parent::tearDown();
    }

    public function test_markdown_posts_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/blog'));
        Filesystem::touch('testSourceDir/blog/test.md');

        MarkdownPost::setSourceDirectory('testSourceDir/blog');

        new StaticPageBuilder(
            MarkdownPost::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/posts/test.html'));
        Filesystem::unlink('_site/posts/test.html');
    }

    public function test_markdown_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/pages'));
        Filesystem::touch('testSourceDir/pages/test.md');

        MarkdownPage::setSourceDirectory('testSourceDir/pages');

        new StaticPageBuilder(
            MarkdownPage::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/test.html'));
        Filesystem::unlink('_site/test.html');
    }

    public function test_documentation_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/documentation'));
        Filesystem::touch('testSourceDir/documentation/test.md');

        DocumentationPage::setSourceDirectory('testSourceDir/documentation');

        new StaticPageBuilder(
            DocumentationPage::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/docs/test.html'));
        Filesystem::unlink('_site/docs/test.html');
    }

    public function test_blade_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/blade'));
        Filesystem::touch('testSourceDir/blade/test.blade.php');

        BladePage::setSourceDirectory('testSourceDir/blade');
        Config::set('view.paths', ['testSourceDir/blade']);

        new StaticPageBuilder(
            BladePage::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/test.html'));
        Filesystem::unlink('_site/test.html');
    }
}
