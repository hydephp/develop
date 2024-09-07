<?php

declare(strict_types=1);

use Hyde\Facades\Filesystem;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Facades\Filesystem
 *
 * @see \Hyde\Framework\Testing\Feature\FilesystemFacadeTest
 */
class FilesystemFacadeMimeTypeHelperUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

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
    }
}
