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

    /**
     * @dataProvider mimeTypeProvider
     */
    public function testFindMimeTypeWithKnownExtensions(string $extension, string $expectedMimeType)
    {
        $this->assertSame($expectedMimeType, Filesystem::findMimeType("file.$extension"));
    }

    /**
     * @dataProvider mimeTypeProvider
     */
    public function testFindMimeTypeWithRemoteUrls(string $extension, string $expectedMimeType)
    {
        $this->assertSame($expectedMimeType, Filesystem::findMimeType("https://example.com/file.$extension"));
    }

    public static function mimeTypeProvider(): array
    {
        return [
            ['txt', 'text/plain'],
            ['md', 'text/markdown'],
            ['html', 'text/html'],
            ['css', 'text/css'],
            ['svg', 'image/svg+xml'],
            ['png', 'image/png'],
            ['jpg', 'image/jpeg'],
            ['jpeg', 'image/jpeg'],
            ['gif', 'image/gif'],
            ['json', 'application/json'],
            ['js', 'application/javascript'],
            ['xml', 'application/xml'],
        ];
    }
}
