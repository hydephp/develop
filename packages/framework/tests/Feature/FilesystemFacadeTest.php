<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Filesystem
 * @covers \Hyde\Foundation\Kernel\Filesystem
 * @covers \Hyde\Framework\Concerns\Internal\ForwardsIlluminateFilesystem
 *
 * @see \Hyde\Framework\Testing\Unit\FilesystemFacadeUnitTest
 */
class FilesystemFacadeTest extends TestCase
{
    public function testBasePath()
    {
        $this->assertSame(Hyde::path(), Filesystem::basePath());
    }

    public function testAbsolutePath()
    {
        $this->assertSame(Hyde::path('foo'), Filesystem::absolutePath('foo'));
        $this->assertSame(Hyde::path('foo'), Filesystem::absolutePath(Hyde::path('foo')));
    }

    public function testRelativePath()
    {
        $this->assertSame('', Filesystem::relativePath(Hyde::path()));
        $this->assertSame('foo', Filesystem::relativePath(Hyde::path('foo')));
        $this->assertSame('foo', Filesystem::relativePath('foo'));
    }

    public function testTouch()
    {
        Filesystem::touch('foo');

        $this->assertFileExists(Hyde::path('foo'));

        Filesystem::unlink('foo');
    }

    public function testUnlink()
    {
        touch(Hyde::path('foo'));

        Filesystem::unlink('foo');

        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function testUnlinkIfExists()
    {
        touch(Hyde::path('foo'));

        Filesystem::unlinkIfExists('foo');

        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function testMethodWithoutMocking()
    {
        $this->assertSame(3, Filesystem::put('foo', 'bar'));
        $this->assertFileExists(Hyde::path('foo'));

        unlink(Hyde::path('foo'));
    }

    public function testMethodWithNamedArguments()
    {
        $this->assertSame(3, Filesystem::put(path: 'foo', contents: 'bar'));
        $this->assertFileExists(Hyde::path('foo'));

        unlink(Hyde::path('foo'));
    }

    public function testMethodWithMixedSequentialAndNamedArguments()
    {
        $this->assertSame(3, Filesystem::put('foo', contents: 'bar'));
        $this->assertFileExists(Hyde::path('foo'));

        unlink(Hyde::path('foo'));
    }

    public function testMethodWithMixedSequentialAndNamedArgumentsSkippingMiddleOne()
    {
        Filesystem::makeDirectory('foo', recursive: true);

        $this->assertDirectoryExists(Hyde::path('foo'));

        rmdir(Hyde::path('foo'));
    }
}
