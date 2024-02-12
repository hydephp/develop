<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\HydeServiceProvider;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Class BuildOutputDirectoryCanBeChangedTest.
 */
class BuildOutputDirectoryCanBeChangedTest extends TestCase
{
    public function testSiteOutputDirectoryCanBeChangedForSiteBuilds()
    {
        $this->file('_posts/test-post.md');

        Hyde::setOutputDirectory('_site/build');

        $this->withoutMockingConsoleOutput();
        $this->artisan('build');

        $this->assertFileExists(Hyde::path('_site/build/posts/test-post.html'));
        $this->assertFileExists(Hyde::path('_site/build/media/app.css'));
        $this->assertFileExists(Hyde::path('_site/build/index.html'));

        File::deleteDirectory(Hyde::path('_site/build'));
    }

    public function testSiteOutputDirectoryCanBeChangedInStaticPageBuilder()
    {
        $this->file('_posts/test-post.md');

        Hyde::setOutputDirectory('_site/build');

        StaticPageBuilder::handle(Pages::getPage('_posts/test-post.md'));

        $this->assertFileExists(Hyde::path('_site/build/posts/test-post.html'));

        File::deleteDirectory(Hyde::path('_site/build'));
    }

    public function testOutputDirectoryIsCreatedIfItDoesNotExistInStaticPageBuilder()
    {
        $this->file('_posts/test-post.md');
        File::deleteDirectory(Hyde::path('_site/build/foo'));
        Hyde::setOutputDirectory('_site/build/foo');
        StaticPageBuilder::handle(Pages::getPage('_posts/test-post.md'));

        $this->assertFileExists(Hyde::path('_site/build/foo/posts/test-post.html'));
        File::deleteDirectory(Hyde::path('_site/build/foo'));
    }

    public function testSiteOutputDirectoryCanBeChangedInConfiguration()
    {
        $this->assertEquals('_site', Hyde::kernel()->getOutputDirectory());

        config(['hyde.output_directory' => '_site/build']);
        (new HydeServiceProvider($this->app))->register();

        $this->assertEquals('_site/build', Hyde::kernel()->getOutputDirectory());

        $this->file('_posts/test-post.md');
        StaticPageBuilder::handle(Pages::getPage('_posts/test-post.md'));
        $this->assertFileExists(Hyde::path('_site/build/posts/test-post.html'));

        File::deleteDirectory(Hyde::path('_site/build'));
    }

    public function testSiteOutputDirectoryPathIsNormalizedToTrimTrailingSlashes()
    {
        Hyde::setOutputDirectory('foo/bar/');
        $this->assertEquals('foo/bar', Hyde::kernel()->getOutputDirectory());
    }
}
