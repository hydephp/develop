<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\BuildRssFeedCommand::class)]
class BuildRssFeedCommandTest extends TestCase
{
    public function testRssFeedIsGeneratedWhenConditionsAreMet()
    {
        $this->withSiteUrl();
        config(['hyde.rss.enabled' => true]);
        $this->file('_posts/foo.md');

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
        $this->artisan('build:rss')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/feed.xml'));
        Filesystem::unlink('_site/feed.xml');
    }

    public function testRssFilenameCanBeChanged()
    {
        $this->withSiteUrl();
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

    public function testRssFeedIsNotGeneratedWithoutSiteUrl()
    {
        config(['hyde.url' => '']);
        $this->file('_posts/foo.md');

        $this->artisan('build:rss')
            ->expectsOutput('Cannot generate the RSS feed as the feature is not enabled')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
    }

    public function testRssFeedIsNotGeneratedWhenFeedIsDisabledInConfig()
    {
        $this->withSiteUrl();
        config(['hyde.rss.enabled' => false]);
        $this->file('_posts/foo.md');

        $this->artisan('build:rss')
            ->expectsOutput('Cannot generate the RSS feed as the feature is not enabled')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
    }

    public function testRssFeedIsNotGeneratedWhenThereAreNoPosts()
    {
        $this->withSiteUrl();

        $this->artisan('build:rss')
            ->expectsOutput('Cannot generate the RSS feed as the feature is not enabled')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist(Hyde::path('_site/feed.xml'));
    }
}
