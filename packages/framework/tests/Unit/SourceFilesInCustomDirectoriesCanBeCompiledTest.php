<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Hyde;
use Hyde\Framework\Models\BladePage;
use Hyde\Framework\Models\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\StaticPageBuilder;
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
        touch(Hyde::path('testSourceDir/blog/test.md'));

        MarkdownPost::$sourceDirectory = 'testSourceDir/blog';

        // Uses the same logic as the BuildActionRunner for an accurate test.
        new StaticPageBuilder(
            DiscoveryService::getParserInstanceForModel(
                MarkdownPost::class,
                'test'
            )->get(),
            true
        );

        $this->assertFileExists(Hyde::path('_site/posts/test.html'));
        unlink(Hyde::path('_site/posts/test.html'));
    }

    public function test_markdown_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/pages'));
        touch(Hyde::path('testSourceDir/pages/test.md'));

        MarkdownPage::$sourceDirectory = 'testSourceDir/pages';

        // Uses the same logic as the BuildActionRunner for an accurate test.
        new StaticPageBuilder(
            DiscoveryService::getParserInstanceForModel(
                MarkdownPage::class,
                'test'
            )->get(),
            true
        );

        $this->assertFileExists(Hyde::path('_site/test.html'));
        unlink(Hyde::path('_site/test.html'));
    }

    public function test_documentation_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/documentation'));
        touch(Hyde::path('testSourceDir/documentation/test.md'));

        DocumentationPage::$sourceDirectory = 'testSourceDir/documentation';

        // Uses the same logic as the BuildActionRunner for an accurate test.
        new StaticPageBuilder(
            DiscoveryService::getParserInstanceForModel(
                DocumentationPage::class,
                'test'
            )->get(),
            true
        );

        $this->assertFileExists(Hyde::path('_site/docs/test.html'));
        unlink(Hyde::path('_site/docs/test.html'));
    }

    public function test_blade_pages_in_changed_directory_can_be_compiled()
    {
        mkdir(Hyde::path('testSourceDir/blade'));
        touch(Hyde::path('testSourceDir/blade/test.blade.php'));

        BladePage::$sourceDirectory = 'testSourceDir/blade';
        Config::set('view.paths', ['testSourceDir/blade']);

        // Uses the same logic as the BuildActionRunner for an accurate test.
        new StaticPageBuilder(
            DiscoveryService::getParserInstanceForModel(
                BladePage::class,
                'test'
            )->get(),
            true
        );

        $this->assertFileExists(Hyde::path('_site/test.html'));
        unlink(Hyde::path('_site/test.html'));
    }
}
