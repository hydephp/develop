<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;

/**
 * @covers \Hyde\Facades\Filesystem
 */
class FilesystemFacadeTest extends TestCase
{
    public function testExists()
    {
        $this->createExpectation('exists', true, Hyde::path('path'));

        Filesystem::exists('path');
    }

    public function testMissing()
    {
        $this->createExpectation('missing', true, Hyde::path('path'));

        Filesystem::missing('path');
    }

    public function testGet()
    {
        $this->createExpectation('get', 'string', Hyde::path('path'), false);

        Filesystem::get('path');
    }

    public function testSharedGet()
    {
        $this->createExpectation('sharedGet', 'string', Hyde::path('path'));

        Filesystem::sharedGet('path');
    }

    public function testGetRequire()
    {
        $this->createExpectation('getRequire', 'string', Hyde::path('path'), []);

        Filesystem::getRequire('path');
    }

    public function testRequireOnce()
    {
        $this->createExpectation('requireOnce', 'string', Hyde::path('path'), []);

        Filesystem::requireOnce('path');
    }

    public function testLines()
    {
        $this->createExpectation('lines', new LazyCollection(), Hyde::path('path'));

        Filesystem::lines('path');
    }

    public function testHash()
    {
        $this->createExpectation('hash', 'string', Hyde::path('path'), 'md5');

        Filesystem::hash('path');
    }

    public function testPut()
    {
        $this->createExpectation('put', 10, Hyde::path('path'), 'contents', false);

        Filesystem::put('path', 'contents');
    }

    public function testReplace()
    {
        $this->createExpectation('replace', null, Hyde::path('path'), 'content');

        Filesystem::replace('path', 'content');
    }

    public function testReplaceInFile()
    {
        $this->createExpectation('replaceInFile', null,'search', 'replace', Hyde::path('path'));

        Filesystem::replaceInFile('search', 'replace', 'path');
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
