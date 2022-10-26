<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Commands\HydeRebuildStaticSiteCommand
 */
class HydeRebuildStaticSiteCommandTest extends TestCase
{
    public function test_handle_is_successful_with_valid_path()
    {
        file_put_contents(Hyde::path('_pages/test-page.md'), 'foo');

        $this->artisan('rebuild '.'_pages/test-page.md')
            ->assertExitCode(0);

        $outputPath = '_site/test-page.html';
        $this->assertFileExists(Hyde::path($outputPath));

        unlink(Hyde::path('_pages/test-page.md'));
        unlink(Hyde::path($outputPath));
    }

    public function test_media_files_can_be_transferred()
    {
        backupDirectory(Hyde::path('_site/media'));
        deleteDirectory(Hyde::path('_site/media'));
        mkdir(Hyde::path('_site/media'));

        Hyde::touch(('_media/test.jpg'));

        $this->artisan('rebuild _media')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/media/test.jpg'));
        unlink(Hyde::path('_media/test.jpg'));
        unlink(Hyde::path('_site/media/test.jpg'));

        restoreDirectory(Hyde::path('_site/media'));
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
        Hyde::touch(('_docs/foo.md'));

        $this->artisan('rebuild _docs/foo.md')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/docs/foo.html'));

        unlink(Hyde::path('_docs/foo.md'));
        unlink(Hyde::path('_site/docs/foo.html'));
    }

    public function test_rebuild_blog_post()
    {
        Hyde::touch(('_posts/foo.md'));

        $this->artisan('rebuild _posts/foo.md')
            ->assertExitCode(0);

        $this->assertFileExists(Hyde::path('_site/posts/foo.html'));

        unlink(Hyde::path('_posts/foo.md'));
        unlink(Hyde::path('_site/posts/foo.html'));
    }
}
