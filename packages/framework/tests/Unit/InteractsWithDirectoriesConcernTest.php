<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Hyde;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Concerns\InteractsWithDirectories::class)]
class InteractsWithDirectoriesConcernTest extends UnitTestCase
{
    use InteractsWithDirectories;

    protected static bool $needsKernel = true;

    /** @var \Illuminate\Filesystem\Filesystem&\Mockery\MockInterface */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = $this->mockFilesystemStrict();
    }

    protected function tearDown(): void
    {
        $this->verifyMockeryExpectations();
    }

    public function testNeedsDirectoryCreatesTheDirectory()
    {
        $this->filesystem->expects('exists')->with(Hyde::path('foo'))->andReturnFalse();
        $this->filesystem->expects('makeDirectory')->with(Hyde::path('foo'), 0755, true);

        $this->needsDirectory('foo');
    }

    public function testNeedsDirectoryCreatesTheDirectoryRecursively()
    {
        $this->filesystem->expects('exists')->with(Hyde::path('foo/bar/baz'))->andReturnFalse();
        $this->filesystem->expects('makeDirectory')->with(Hyde::path('foo/bar/baz'), 0755, true);

        $this->needsDirectory('foo/bar/baz');
    }

    public function testNeedsDirectoryHandlesExistingDirectory()
    {
        $this->filesystem->expects('exists')->with(Hyde::path('foo'))->andReturnTrue()->twice();
        $this->filesystem->expects('makeDirectory')->never();

        $this->needsDirectory('foo');
        $this->needsDirectory('foo');
    }

    public function testNeedsDirectoriesCreatesSingleDirectory()
    {
        $this->filesystem->expects('exists')->with(Hyde::path('foo'))->andReturnFalse();
        $this->filesystem->expects('makeDirectory')->with(Hyde::path('foo'), 0755, true);

        $this->needsDirectories(['foo']);
    }

    public function testNeedsDirectoriesCreatesMultipleDirectories()
    {
        $this->filesystem->expects('exists')->with(Hyde::path('foo'))->andReturnFalse();
        $this->filesystem->expects('exists')->with(Hyde::path('bar'))->andReturnFalse();
        $this->filesystem->expects('makeDirectory')->with(Hyde::path('foo'), 0755, true);
        $this->filesystem->expects('makeDirectory')->with(Hyde::path('bar'), 0755, true);

        $this->needsDirectories(['foo', 'bar']);
    }

    public function testNeedsParentDirectoryCreatesDirectoryForTheParentFile()
    {
        $this->filesystem->expects('exists')->with(Hyde::path('foo/bar'))->andReturnFalse();
        $this->filesystem->expects('makeDirectory')->with(Hyde::path('foo/bar'), 0755, true);

        $this->needsParentDirectory(Hyde::path('foo/bar/baz.txt'));
    }

    public function testMethodsCanBeCalledStatically()
    {
        $this->filesystem->expects('exists')->with(Hyde::path('foo'))->andReturnFalse()->twice();
        $this->filesystem->expects('makeDirectory')->with(Hyde::path('foo'), 0755, true)->twice();

        static::needsDirectory('foo');
        static::needsDirectories(['foo']);
    }
}
