<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * Class InteractsWithDirectoriesConcernTest.
 *
 * @covers \Hyde\Framework\Concerns\InteractsWithDirectories
 */
class InteractsWithDirectoriesConcernTest extends TestCase
{
    use InteractsWithDirectories;

    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(Hyde::path('foo'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(Hyde::path('foo'));

        parent::tearDown();
    }

    public function testMethodsCanBeCalledStatically()
    {
        static::needsDirectory('foo');
        $this->assertDirectoryExists(Hyde::path('foo'));

        static::needsDirectories(['foo']);
        $this->assertDirectoryExists(Hyde::path('foo'));
    }

    public function testNeedsDirectoryCreatesTheDirectory()
    {
        $this->needsDirectory('foo');
        $this->assertDirectoryExists(Hyde::path('foo'));
    }

    public function testNeedsDirectoryCreatesTheDirectoryRecursively()
    {
        $this->needsDirectory('foo/bar/baz');
        $this->assertDirectoryExists(Hyde::path('foo/bar/baz'));
    }

    public function testNeedsDirectoryHandlesExistingDirectory()
    {
        $this->needsDirectory('foo');
        $this->needsDirectory('foo');
        $this->assertDirectoryExists(Hyde::path('foo'));
    }

    public function testNeedsDirectoriesCreatesSingleDirectory()
    {
        $this->needsDirectories(['foo']);
        $this->assertDirectoryExists(Hyde::path('foo'));
    }

    public function testNeedsDirectoriesCreatesMultipleDirectories()
    {
        $this->needsDirectories(['foo', 'bar']);
        $this->assertDirectoryExists(Hyde::path('foo'));
        $this->assertDirectoryExists(Hyde::path('bar'));

        File::deleteDirectory(Hyde::path('bar'));
    }

    public function testNeedsParentDirectoryCreatesDirectoryForTheParentFile()
    {
        $this->needsParentDirectory(Hyde::path('foo/bar/baz.txt'));
        $this->assertDirectoryExists(Hyde::path('foo/bar'));
    }
}
