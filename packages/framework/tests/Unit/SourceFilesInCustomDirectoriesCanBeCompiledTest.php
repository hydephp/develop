<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

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
        Hyde::touch(('testSourceDir/blog/test.md'));

        MarkdownPost::$sourceDirectory = 'testSourceDir/blog';

        new StaticPageBuilder(
            MarkdownPost::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/posts/test.html'));
        unlink(Hyde::path('_site/posts/test.html'));
    }

    public function test_markdown_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/pages'));
        Hyde::touch(('testSourceDir/pages/test.md'));

        MarkdownPage::$sourceDirectory = 'testSourceDir/pages';

        new StaticPageBuilder(
            MarkdownPage::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/test.html'));
        unlink(Hyde::path('_site/test.html'));
    }

    public function test_documentation_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/documentation'));
        Hyde::touch(('testSourceDir/documentation/test.md'));

        DocumentationPage::$sourceDirectory = 'testSourceDir/documentation';

        new StaticPageBuilder(
            DocumentationPage::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/docs/test.html'));
        unlink(Hyde::path('_site/docs/test.html'));
    }

    public function test_blade_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/blade'));
        Hyde::touch(('testSourceDir/blade/test.blade.php'));

        BladePage::$sourceDirectory = 'testSourceDir/blade';
        Config::set('view.paths', ['testSourceDir/blade']);

        new StaticPageBuilder(
            BladePage::parse('test'),
            true
        );

        $this->assertFileExists(Hyde::path('_site/test.html'));
        unlink(Hyde::path('_site/test.html'));
    }
}
