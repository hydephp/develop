<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\TransferStaticFiles;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

#[\PHPUnit\Framework\Attributes\CoversClass(TransferStaticFiles::class)]
class StaticFilePassthroughTest extends TestCase
{
    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::sitePath());

        parent::tearDown();
    }

    public function testStaticFilesAreCopiedVerbatimToTheSiteRoot(): void
    {
        $binary = "\x00\x01\x02\xff";

        $this->file('_static/robots.txt', "User-agent: *\nAllow: /");
        $this->file('_static/.well-known/security.txt', 'Contact: mailto:security@example.com');
        $this->file('_static/favicon.ico', $binary);

        $this->artisan('build')->assertExitCode(0);

        $this->assertSame("User-agent: *\nAllow: /", Filesystem::getContents('_site/robots.txt'));
        $this->assertSame('Contact: mailto:security@example.com', Filesystem::getContents('_site/.well-known/security.txt'));
        $this->assertSame($binary, Filesystem::getContents('_site/favicon.ico'));
    }

    public function testAbsentStaticDirectoryIsIgnored(): void
    {
        $this->artisan('build')->assertExitCode(0);

        $this->assertDirectoryDoesNotExist(Hyde::path('_static'));
    }

    public function testStaticFilesOverwriteTheirOutputFromThePreviousBuild(): void
    {
        $this->file('_static/robots.txt', 'first');
        $this->artisan('build')->assertExitCode(0);

        $this->file('_static/robots.txt', 'second');
        $this->artisan('build')->assertExitCode(0);

        $this->assertSame('second', Filesystem::getContents('_site/robots.txt'));
    }

    public function testStaticFilesCannotOverwriteGeneratedOutput(): void
    {
        $this->file('_static/a.txt', 'copied first without preflight');
        $this->file('_static/index.html', 'static replacement');

        try {
            $this->artisan('build')->run();
            $this->fail('The conflicting static file was not rejected.');
        } catch (FileConflictException $exception) {
            $this->assertSame('File [_site/index.html] already exists.', $exception->getMessage());
        }

        $this->assertFileDoesNotExist(Hyde::sitePath('a.txt'));
        $this->assertStringNotContainsString('static replacement', Filesystem::getContents('_site/index.html'));
    }

    public function testStaticFilesCannotOverwriteTransferredMedia(): void
    {
        $this->file('_media/static-collision.jpg', 'media');
        $this->file('_static/a.txt', 'copied first without preflight');
        $this->file('_static/media/static-collision.jpg', 'static');

        try {
            $this->artisan('build')->run();
            $this->fail('The conflicting static file was not rejected.');
        } catch (FileConflictException $exception) {
            $this->assertSame('File [_site/media/static-collision.jpg] already exists.', $exception->getMessage());
        } finally {
            Filesystem::unlink('_media/static-collision.jpg');
        }

        $this->assertFileDoesNotExist(Hyde::sitePath('a.txt'));
        $this->assertSame('media', Filesystem::getContents('_site/media/static-collision.jpg'));
    }

    public function testDisabledMediaTransferDoesNotCreateAStaticFileCollision(): void
    {
        config(['hyde.transfer_media_assets' => false]);
        $this->file('_media/static-collision.jpg', 'media');
        $this->file('_static/media/static-collision.jpg', 'static');

        try {
            $this->artisan('build')->assertExitCode(0);
            $this->assertSame('static', Filesystem::getContents('_site/media/static-collision.jpg'));
        } finally {
            Filesystem::unlink('_media/static-collision.jpg');
        }
    }
}
