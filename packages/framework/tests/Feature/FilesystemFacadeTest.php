<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;

/**
 * @covers \Hyde\Facades\Filesystem
 * @covers \Hyde\Foundation\Filesystem
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

    public function testSmartGlob()
    {
        $this->createExpectation('glob', [
            Hyde::path('foo'),
            Hyde::path('bar'),
            Hyde::path('baz'),
        ], Hyde::path('pattern/*.md'), 0);

        $this->assertEquals(
            Collection::make(['foo', 'bar', 'baz']),
            Filesystem::smartGlob('pattern/*.md')
        );
    }

    public function testTouch()
    {
        Filesystem::touch('foo');

        $this->assertFileExists(Hyde::path('foo'));
        unlink(Hyde::path('foo'));
    }

    public function testUnlink()
    {
        touch(Hyde::path('foo'));
        Filesystem::unlink('foo');

        $this->assertFileDoesNotExist(Hyde::path('foo'));
    }

    public function testGetContents()
    {
        $this->createExpectation('get', 'string', Hyde::path('path'), false);

        Filesystem::getContents('path');
    }

    public function testPutContents()
    {
        $this->createExpectation('put', true, Hyde::path('path'), 'string', false);

        Filesystem::putContents('path', 'string');
    }

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
        $this->createExpectation('replaceInFile', null, 'search', 'replace', Hyde::path('path'));

        Filesystem::replaceInFile('search', 'replace', 'path');
    }

    public function testPrepend()
    {
        $this->createExpectation('prepend', 10, Hyde::path('path'), 'data');

        Filesystem::prepend('path', 'data');
    }

    public function testAppend()
    {
        $this->createExpectation('append', 10, Hyde::path('path'), 'data');

        Filesystem::append('path', 'data');
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

    public function testDeleteWithArray()
    {
        $this->createExpectation('delete', true, [Hyde::path('path'), Hyde::path('another')]);

        Filesystem::delete(['path', 'another']);
    }

    public function testMove()
    {
        $this->createExpectation('move', true, Hyde::path('path'), Hyde::path('target'));

        Filesystem::move('path', 'target');
    }

    public function testCopy()
    {
        $this->createExpectation('copy', true, Hyde::path('path'), Hyde::path('target'));

        Filesystem::copy('path', 'target');
    }

    public function testLink()
    {
        $this->createExpectation('link', true, Hyde::path('target'), Hyde::path('link'));

        Filesystem::link('target', 'link');
    }

    public function testRelativeLink()
    {
        $this->createExpectation('relativeLink', true, Hyde::path('target'), Hyde::path('link'));

        Filesystem::relativeLink('target', 'link');
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
        $this->createExpectation('isDirectory', true, Hyde::path('directory'));

        Filesystem::isDirectory('directory');
    }

    public function testIsEmptyDirectory()
    {
        $this->createExpectation('isEmptyDirectory', true, Hyde::path('directory'), false);

        Filesystem::isEmptyDirectory('directory');
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
        $this->createExpectation('isFile', true, Hyde::path('file'));

        Filesystem::isFile('file');
    }

    public function testGlob()
    {
        $this->createExpectation('glob', [], Hyde::path('pattern'), 0);

        Filesystem::glob('pattern');
    }

    public function testFiles()
    {
        $this->createExpectation('files', [], Hyde::path('directory'), false);

        Filesystem::files('directory');
    }

    public function testAllFiles()
    {
        $this->createExpectation('allFiles', [], Hyde::path('directory'), false);

        Filesystem::allFiles('directory');
    }

    public function testDirectories()
    {
        $this->createExpectation('directories', [], Hyde::path('directory'));

        Filesystem::directories('directory');
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
        $this->createExpectation('moveDirectory', true, Hyde::path('from'), Hyde::path('to'), false);

        Filesystem::moveDirectory('from', 'to');
    }

    public function testCopyDirectory()
    {
        $this->createExpectation('copyDirectory', true, Hyde::path('directory'), Hyde::path('destination'), false);

        Filesystem::copyDirectory('directory', 'destination');
    }

    public function testDeleteDirectory()
    {
        $this->createExpectation('deleteDirectory', true, Hyde::path('directory'), false);

        Filesystem::deleteDirectory('directory');
    }

    public function testDeleteDirectories()
    {
        $this->createExpectation('deleteDirectories', true, Hyde::path('directory'));

        Filesystem::deleteDirectories('directory');
    }

    public function testCleanDirectory()
    {
        $this->createExpectation('cleanDirectory', true, Hyde::path('directory'));

        Filesystem::cleanDirectory('directory');
    }

    protected function createExpectation(string $method, mixed $returns, ...$args): void
    {
        File::shouldReceive($method)->withArgs($args)->once()->andReturn($returns);
    }
}
