<?php

declare(strict_types=1);

use Hyde\Facades\Filesystem;
use Hyde\Testing\UnitTestCase;

/**
 * @see \Hyde\Framework\Testing\Feature\FilesystemFacadeTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Facades\Filesystem::class)]
class FilesystemFacadeMimeTypeHelperUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    #[\PHPUnit\Framework\Attributes\DataProvider('mimeTypeProvider')]
    public function testFindMimeTypeWithKnownExtensions(string $extension, string $expectedMimeType)
    {
        $this->assertSame($expectedMimeType, Filesystem::findMimeType("file.$extension"));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('mimeTypeProvider')]
    public function testFindMimeTypeWithRemoteUrls(string $extension, string $expectedMimeType)
    {
        $this->assertSame($expectedMimeType, Filesystem::findMimeType("https://example.com/file.$extension"));
    }

    public function testFindMimeTypeWithUnknownExtension()
    {
        $this->assertSame('text/plain', Filesystem::findMimeType('file.unknown'));
    }

    public function testFindMimeTypeWithFileWithoutExtension()
    {
        $this->assertSame('text/plain', Filesystem::findMimeType('file'));
    }

    public function testFindMimeTypeWithRelativePath()
    {
        $this->assertSame('text/plain', Filesystem::findMimeType('path/to/file.txt'));
    }

    public function testFindMimeTypeWithAbsolutePath()
    {
        $this->assertSame('text/plain', Filesystem::findMimeType('/absolute/path/to/file.txt'));
    }

    public function testFindMimeTypeWithUrl()
    {
        $this->assertSame('text/html', Filesystem::findMimeType('https://example.com/page.html'));
    }

    public function testFindMimeTypeWithCaseSensitivity()
    {
        $this->assertSame('text/plain', Filesystem::findMimeType('file.TXT'));
    }

    public static function mimeTypeProvider(): \Iterator
    {
        yield ['txt', 'text/plain'];
        yield ['md', 'text/markdown'];
        yield ['html', 'text/html'];
        yield ['css', 'text/css'];
        yield ['svg', 'image/svg+xml'];
        yield ['png', 'image/png'];
        yield ['jpg', 'image/jpeg'];
        yield ['jpeg', 'image/jpeg'];
        yield ['gif', 'image/gif'];
        yield ['json', 'application/json'];
        yield ['js', 'application/javascript'];
        yield ['xml', 'application/xml'];
    }
}
