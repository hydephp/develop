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
        $this->createExpectation('prepend', 10, Hyde::path('path'), 'content');

        Filesystem::prepend('path', 'content');
    }

    public function testAppend()
    {
        $this->createExpectation('append', 10, Hyde::path('path'), 'content');

        Filesystem::append('path', 'content');
    }

    public function testChmod()
    {
        $this->createExpectation('chmod', null, Hyde::path('path'), 0755);

        Filesystem::chmod('path', 0755);
    }

    public function testDelete()
    {
        $this->createExpectation('delete', true, Hyde::path('path'));

        Filesystem::delete('path');
    }

    public function testMove()
    {
        $this->createExpectation('move', true, Hyde::path('path'), Hyde::path('newPath'));

        Filesystem::move('path', 'newPath');
    }

    public function testCopy()
    {
        $this->createExpectation('copy', true, Hyde::path('path'), Hyde::path('newPath'));

        Filesystem::copy('path', 'newPath');
    }

    public function testLink()
    {
        $this->createExpectation('link', true, Hyde::path('path'), Hyde::path('newPath'));

        Filesystem::link('path', 'newPath');
    }

    public function testRelativeLink()
    {
        $this->createExpectation('relativeLink', true, Hyde::path('path'), Hyde::path('newPath'));

        Filesystem::relativeLink('path', 'newPath');
    }

    public function testName()
    {
        $this->createExpectation('name', 'string', Hyde::path('path'));

        Filesystem::name('path');
    }

    public function testBasename()
    {
        $this->createExpectation('basename', 'string', Hyde::path('path'));

        Filesystem::basename('path');
    }

    public function testDirname()
    {
        $this->createExpectation('dirname', 'string', Hyde::path('path'));

        Filesystem::dirname('path');
    }

    public function testExtension()
    {
        $this->createExpectation('extension', 'string', Hyde::path('path'));

        Filesystem::extension('path');
    }

    public function testGuessExtension()
    {
        $this->createExpectation('guessExtension', 'string', Hyde::path('path'));

        Filesystem::guessExtension('path');
    }

    public function testType()
    {
        $this->createExpectation('type', 'string', Hyde::path('path'));

        Filesystem::type('path');
    }

    public function testMimeType()
    {
        $this->createExpectation('mimeType', 'string', Hyde::path('path'));

        Filesystem::mimeType('path');
    }

    public function testSize()
    {
        $this->createExpectation('size', 10, Hyde::path('path'));

        Filesystem::size('path');
    }

    public function testLastModified()
    {
        $this->createExpectation('lastModified', 10, Hyde::path('path'));

        Filesystem::lastModified('path');
    }

    public function testIsDirectory()
    {
        $this->createExpectation('isDirectory', true, Hyde::path('path'));

        Filesystem::isDirectory('path');
    }

    public function testIsEmptyDirectory()
    {
        $this->createExpectation('isEmptyDirectory', true, Hyde::path('path'), false);

        Filesystem::isEmptyDirectory('path');
    }

    public function testIsReadable()
    {
        $this->createExpectation('isReadable', true, Hyde::path('path'));

        Filesystem::isReadable('path');
    }

    public function testIsWritable()
    {
        $this->createExpectation('isWritable', true, Hyde::path('path'));

        Filesystem::isWritable('path');
    }

    public function testHasSameHash()
    {
        $this->createExpectation('hasSameHash', true, Hyde::path('firstFile'), Hyde::path('secondFile'));

        Filesystem::hasSameHash('firstFile', 'secondFile');
    }

    public function testIsFile()
    {
        $this->createExpectation('isFile', true, Hyde::path('path'));

        Filesystem::isFile('path');
    }

    public function testGlob()
    {
        $this->createExpectation('glob', [], Hyde::path('path'), 0);

        Filesystem::glob('path');
    }

    public function testFiles()
    {
        $this->createExpectation('files', [], Hyde::path('path'), false);

        Filesystem::files('path');
    }

    public function testAllFiles()
    {
        $this->createExpectation('allFiles', [], Hyde::path('path'), false);

        Filesystem::allFiles('path');
    }

    public function testDirectories()
    {
        $this->createExpectation('directories', [], Hyde::path('path'));

        Filesystem::directories('path');
    }

    public function testEnsureDirectoryExists()
    {
        $this->createExpectation('ensureDirectoryExists', null, Hyde::path('path'), 0755, true);

        Filesystem::ensureDirectoryExists('path');
    }

    public function testMakeDirectory()
    {
        $this->createExpectation('makeDirectory', true, Hyde::path('path'), 0755, false, false);

        Filesystem::makeDirectory('path');
    }

    public function testMoveDirectory()
    {
        $this->createExpectation('moveDirectory', true, Hyde::path('path'), Hyde::path('newPath'), false);

        Filesystem::moveDirectory('path', 'newPath');
    }

    public function testCopyDirectory()
    {
        $this->createExpectation('copyDirectory', true, Hyde::path('path'), Hyde::path('newPath'), false);

        Filesystem::copyDirectory('path', 'newPath');
    }

    public function testDeleteDirectory()
    {
        $this->createExpectation('deleteDirectory', true, Hyde::path('path'), false);

        Filesystem::deleteDirectory('path');
    }

    public function testDeleteDirectories()
    {
        $this->createExpectation('deleteDirectories', true, Hyde::path('path'));

        Filesystem::deleteDirectories('path');
    }

    public function testCleanDirectory()
    {
        $this->createExpectation('cleanDirectory', true, Hyde::path('path'));

        Filesystem::cleanDirectory('path');
    }

    protected function createExpectation(string $method, mixed $returns, ...$args): void
    {
        File::shouldReceive($method)
            ->withArgs($args)
            ->once()
            ->andReturn($returns);
    }
}
