<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\RebuildStaticSiteCommand
 */
class RebuildStaticSiteCommandTest extends TestCase
{
    public function test_handle_is_successful_with_valid_path()
    {
        $this->file('_pages/test-page.md', 'foo');

        $this->artisan('rebuild '.'_pages/test-page.md')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/test-page.html'));

        $this->resetSite();
    }

    public function test_media_files_can_be_transferred()
    {
        $this->directory(Hyde::path('_site/media'));
        $this->file('_media/test.jpg');

        $this->artisan('rebuild _media')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/media/test.jpg'));
    }

    public function test_validate_catches_bad_source_directory()
    {
        $this->artisan('rebuild foo/bar')
            ->expectsOutput('Path [foo/bar] is not in a valid source directory.')
            ->assertExitCode(400);
    }

    public function test_validate_catches_missing_file()
    {
        $this->artisan('rebuild _pages/foo.md')
            ->expectsOutput('File [_pages/foo.md] not found.')
            ->assertExitCode(404);
    }

    public function test_rebuild_documentation_page()
    {
        $this->file('_docs/foo.md');

        $this->artisan('rebuild _docs/foo.md')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/foo.html'));

        $this->resetSite();
    }

    public function test_rebuild_blog_post()
    {
        $this->file('_posts/foo.md');

        $this->artisan('rebuild _posts/foo.md')->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/posts/foo.html'));

        $this->resetSite();
    }
}
