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
 * @covers \Hyde\Foundation\Kernel\Filesystem
 * @covers \Hyde\Framework\Concerns\Internal\ForwardsIlluminateFilesystem
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

        $expected = Collection::make(['foo', 'bar', 'baz']);
        $actual = Filesystem::smartGlob('pattern/*.md');

        $this->assertEquals($expected, $actual);
        $this->assertSame($expected->all(), $actual->all());
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
        $this->createExpectation('get', 'string', Hyde::path('path'));

        Filesystem::get('path');
    }

    public function testSharedGet()
    {
        $this->createExpectation('sharedGet', 'string', Hyde::path('path'));

        Filesystem::sharedGet('path');
    }

    public function testGetRequire()
    {
        $this->createExpectation('getRequire', 'string', Hyde::path('path'));

        Filesystem::getRequire('path');
    }

    public function testRequireOnce()
    {
        $this->createExpectation('requireOnce', 'string', Hyde::path('path'));

        Filesystem::requireOnce('path');
    }

    public function testLines()
    {
        $this->createExpectation('lines', new LazyCollection(), Hyde::path('path'));

        Filesystem::lines('path');
    }

    public function testHash()
    {
        $this->createExpectation('hash', 'string', Hyde::path('path'));

        Filesystem::hash('path');
    }

    public function testPut()
    {
        $this->createExpectation('put', 10, Hyde::path('path'), 'contents');

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
        $this->createExpectation('isEmptyDirectory', true, Hyde::path('directory'));

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
        $this->createExpectation('glob', [], Hyde::path('pattern'));

        Filesystem::glob('pattern');
    }

    public function testFiles()
    {
        $this->createExpectation('files', [], Hyde::path('directory'));

        Filesystem::files('directory');
    }

    public function testAllFiles()
    {
        $this->createExpectation('allFiles', [], Hyde::path('directory'));

        Filesystem::allFiles('directory');
    }

    public function testDirectories()
    {
        $this->createExpectation('directories', [], Hyde::path('directory'));

        Filesystem::directories('directory');
    }

    public function testEnsureDirectoryExists()
    {
        $this->createExpectation('ensureDirectoryExists', null, Hyde::path('path'));

        Filesystem::ensureDirectoryExists('path');
    }

    public function testMakeDirectory()
    {
        $this->createExpectation('makeDirectory', true, Hyde::path('path'));

        Filesystem::makeDirectory('path');
    }

    public function testMoveDirectory()
    {
        $this->createExpectation('moveDirectory', true, Hyde::path('from'), Hyde::path('to'));

        Filesystem::moveDirectory('from', 'to');
    }

    public function testCopyDirectory()
    {
        $this->createExpectation('copyDirectory', true, Hyde::path('directory'), Hyde::path('destination'));

        Filesystem::copyDirectory('directory', 'destination');
    }

    public function testDeleteDirectory()
    {
        $this->createExpectation('deleteDirectory', true, Hyde::path('directory'));

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

    public function testFindMimeType()
    {
        // Test known extensions
        $this->assertSame('text/plain', Filesystem::findMimeType('file.txt'));
        $this->assertSame('text/markdown', Filesystem::findMimeType('file.md'));
        $this->assertSame('text/html', Filesystem::findMimeType('file.html'));
        $this->assertSame('text/css', Filesystem::findMimeType('file.css'));
        $this->assertSame('image/svg+xml', Filesystem::findMimeType('file.svg'));
        $this->assertSame('image/png', Filesystem::findMimeType('file.png'));
        $this->assertSame('image/jpeg', Filesystem::findMimeType('file.jpg'));
        $this->assertSame('image/jpeg', Filesystem::findMimeType('file.jpeg'));
        $this->assertSame('image/gif', Filesystem::findMimeType('file.gif'));
        $this->assertSame('application/json', Filesystem::findMimeType('file.json'));
        $this->assertSame('application/javascript', Filesystem::findMimeType('file.js'));
        $this->assertSame('application/xml', Filesystem::findMimeType('file.xml'));

        // Test with remote URLs
        $this->assertSame('text/plain', Filesystem::findMimeType('https://example.com/file.txt'));
        $this->assertSame('text/markdown', Filesystem::findMimeType('https://example.com/file.md'));
        $this->assertSame('text/html', Filesystem::findMimeType('https://example.com/file.html'));
        $this->assertSame('text/css', Filesystem::findMimeType('https://example.com/file.css'));
        $this->assertSame('image/svg+xml', Filesystem::findMimeType('https://example.com/file.svg'));
        $this->assertSame('image/png', Filesystem::findMimeType('https://example.com/file.png'));
        $this->assertSame('image/jpeg', Filesystem::findMimeType('https://example.com/file.jpg'));
        $this->assertSame('image/jpeg', Filesystem::findMimeType('https://example.com/file.jpeg'));
        $this->assertSame('image/gif', Filesystem::findMimeType('https://example.com/file.gif'));
        $this->assertSame('application/json', Filesystem::findMimeType('https://example.com/file.json'));
        $this->assertSame('application/javascript', Filesystem::findMimeType('https://example.com/file.js'));
        $this->assertSame('application/xml', Filesystem::findMimeType('https://example.com/file.xml'));

        // Test unknown extension
        $this->assertSame('text/plain', Filesystem::findMimeType('file.unknown'));

        // Test file without extension
        $this->assertSame('text/plain', Filesystem::findMimeType('file'));

        // Test relative path
        $this->assertSame('text/plain', Filesystem::findMimeType('path/to/file.txt'));

        // Test absolute path
        $this->assertSame('text/plain', Filesystem::findMimeType('/absolute/path/to/file.txt'));

        // Test URL
        $this->assertSame('text/html', Filesystem::findMimeType('https://example.com/page.html'));

        // Test case sensitivity
        $this->assertSame('text/plain', Filesystem::findMimeType('file.TXT'));

        // Test fileinfo fallback for existing files where the extension is not in the lookup table
        $this->file('text.unknown', 'text');
        $this->assertSame('text/plain', Filesystem::findMimeType('text.unknown'));

        $this->file('blank.unknown', '');
        $this->assertSame('application/x-empty', Filesystem::findMimeType('blank.unknown'));

        $this->file('empty.unknown');
        $this->assertSame('application/x-empty', Filesystem::findMimeType('empty.unknown'));

        $this->file('json.unknown', '{"key": "value"}');
        $this->assertSame('application/json', Filesystem::findMimeType('json.unknown'));

        $this->file('xml.unknown', '<?xml version="1.0" encoding="UTF-8"?><root></root>');
        $this->assertSame('text/xml', Filesystem::findMimeType('xml.unknown'));

        $this->file('html.unknown', '<!DOCTYPE html><html><head><title>Test</title></head><body></body></html>');
        $this->assertSame('text/html', Filesystem::findMimeType('html.unknown'));

        $this->file('yaml.unknown', 'key: value');
        $this->assertSame('text/plain', Filesystem::findMimeType('yaml.unknown')); // YAML is not detected by fileinfo

        $this->file('css.unknown', 'body { color: red; }');
        $this->assertSame('text/plain', Filesystem::findMimeType('css.unknown')); // CSS is not detected by fileinfo

        $this->file('js.unknown', 'console.log("Hello, World!");');
        $this->assertSame('text/plain', Filesystem::findMimeType('js.unknown')); // JavaScript is not detected by fileinfo

        $this->file('binary.unknown', "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F"); // 16 bytes of binary data
        $this->assertSame('application/octet-stream', Filesystem::findMimeType('binary.unknown'));

        $this->file('png.unknown', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkAAIAAAoAAvM1P4AAAAASUVORK5CYII=')); // 1x1 transparent PNG
        $this->assertSame('image/png', Filesystem::findMimeType('png.unknown'));

        $this->file('jpeg.unknown', base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/4QA6RXhpZgAATU0AKgAAAAgAA1IBAAABAAAAngIBAAABAAAAnwICAAABAAAAnQ==')); // 1x1 JPEG
        $this->assertSame('image/jpeg', Filesystem::findMimeType('jpeg.unknown'));

        $this->file('gif.unknown', base64_decode('R0lGODlhAQABAIAAAP///wAAACwAAAAAAQABAAACAkQBADs=')); // 1x1 GIF
        $this->assertSame('image/gif', Filesystem::findMimeType('gif.unknown'));

        // Test non-existing file
        $this->assertSame('text/plain', Filesystem::findMimeType('non_existing_file.txt'));

        // Test it uses lookup before fileinfo (so lookup overrides fileinfo)
        $this->file('file.png', 'Not PNG content');
        $this->assertSame('image/png', Filesystem::findMimeType('file.png'));

        $this->file('png.txt', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkAAIAAAoAAvM1P4AAAAASUVORK5CYII=')); // 1x1 transparent PNG
        $this->assertSame('text/plain', Filesystem::findMimeType('png.txt'));
    }

    protected function createExpectation(string $method, mixed $returns, ...$args): void
    {
        File::shouldReceive($method)->withArgs($args)->once()->andReturn($returns);
    }
}
