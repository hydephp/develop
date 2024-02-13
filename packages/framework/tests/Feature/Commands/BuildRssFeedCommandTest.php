<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\BuildRssFeedCommand
 * @covers \Hyde\Framework\Actions\PostBuildTasks\GenerateRssFeed
 */
class BuildRssFeedCommandTest extends TestCase
{
    public function testRssFeedIsGeneratedWhenConditionsAreMet()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->artisan('build:rss')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/feed.xml'));
        Filesystem::unlink('_site/feed.xml');
    }

    public function testRssFilenameCanBeChanged()
    {
        config(['hyde.url' => 'https://example.com']);
        config(['hyde.rss.enabled' => true]);
        config(['hyde.rss.filename' => 'blog.xml']);
        $this->file('_posts/foo.md');

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->assertFileDoesNotExist(Hyde::path('_site/blog.xml'));

        $this->artisan('build:rss')->assertExitCode(0);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->assertFileExists(Hyde::path('_site/blog.xml'));
        Filesystem::unlink('_site/blog.xml');
    }
}
