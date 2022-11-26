<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Facades\Filesystem
 */
class FilesystemFacadeTest extends TestCase
{
    public function testExists()
    {
        $this->createExpectation('exists', true, Hyde::path('foo'));

        Filesystem::exists('foo');
    }

    public function testMissing()
    {
        $this->createExpectation('missing', true, Hyde::path('foo'));

        Filesystem::missing('foo');
    }

    public function testGet()
    {
        //
    }

    public function testSharedGet()
    {
        //
    }

    public function testGetRequire()
    {
        //
    }

    public function testRequireOnce()
    {
        //
    }

    public function testLines()
    {
        //
    }

    public function testHash()
    {
        //
    }

    public function testPut()
    {
        //
    }

    public function testReplace()
    {
        //
    }

    public function testReplaceInFile()
    {
        //
    }

    public function testPrepend()
    {
        //
    }

    public function testAppend()
    {
        //
    }

    public function testChmod()
    {
        //
    }

    public function testDelete()
    {
        //
    }

    public function testMove()
    {
        //
    }

    public function testCopy()
    {
        //
    }

    public function testLink()
    {
        //
    }

    public function testRelativeLink()
    {
        //
    }

    public function testName()
    {
        //
    }

    public function testBasename()
    {
        //
    }

    public function testDirname()
    {
        //
    }

    public function testExtension()
    {
        //
    }

    public function testGuessExtension()
    {
        //
    }

    public function testType()
    {
        //
    }

    public function testMimeType()
    {
        //
    }

    public function testSize()
    {
        //
    }

    public function testLastModified()
    {
        //
    }

    public function testIsDirectory()
    {
        //
    }

    public function testIsEmptyDirectory()
    {
        //
    }

    public function testIsReadable()
    {
        //
    }

    public function testIsWritable()
    {
        //
    }

    public function testHasSameHash()
    {
        //
    }

    public function testIsFile()
    {
        //
    }

    public function testGlob()
    {
        //
    }

    public function testFiles()
    {
        //
    }

    public function testAllFiles()
    {
        //
    }

    public function testDirectories()
    {
        //
    }

    public function testEnsureDirectoryExists()
    {
        //
    }

    public function testMakeDirectory()
    {
        //
    }

    public function testMoveDirectory()
    {
        //
    }

    public function testCopyDirectory()
    {
        //
    }

    public function testDeleteDirectory()
    {
        //
    }

    public function testDeleteDirectories()
    {
        //
    }

    public function testCleanDirectory()
    {
        //
    }

    protected function createExpectation(string $method, mixed $returns, ...$args): void
    {
        File::shouldReceive($method)
            ->withArgs($args)
            ->once()
            ->andReturn($returns);
    }
}
