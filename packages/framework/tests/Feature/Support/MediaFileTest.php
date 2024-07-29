<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Framework\Exceptions\FileNotFoundException;

/**
 * @covers \Hyde\Support\Filesystem\MediaFile
 *
 * @see \Hyde\Framework\Testing\Unit\Support\MediaFileUnitTest
 */
class MediaFileTest extends TestCase
{
    public function testMediaFileCreationAndBasicProperties()
    {
        $this->file('_media/test.txt', 'Hello, World!');

        $mediaFile = MediaFile::make('test.txt');

        $this->assertInstanceOf(MediaFile::class, $mediaFile);
        $this->assertEquals('test.txt', $mediaFile->getName());
        $this->assertEquals('_media/test.txt', $mediaFile->getPath());
        $this->assertEquals(Hyde::path('_media/test.txt'), $mediaFile->getAbsolutePath());
        $this->assertEquals('Hello, World!', $mediaFile->getContents());
        $this->assertEquals('txt', $mediaFile->getExtension());
    }

    public function testMediaFileDiscovery()
    {
        // App.css is a default file
        $this->file('_media/image.png', 'PNG content');
        $this->file('_media/style.css', 'CSS content');
        $this->file('_media/script.js', 'JS content');

        $allFiles = MediaFile::all();

        $this->assertCount(4, $allFiles);
        $this->assertArrayHasKey('image.png', $allFiles);
        $this->assertArrayHasKey('style.css', $allFiles);
        $this->assertArrayHasKey('script.js', $allFiles);

        $fileNames = MediaFile::files();
        $this->assertEquals(['image.png', 'app.css', 'style.css', 'script.js'], $fileNames);
    }

    public function testMediaFileProperties()
    {
        $content = str_repeat('a', 1024); // 1KB content
        $this->file('_media/large_file.txt', $content);

        $mediaFile = MediaFile::make('large_file.txt');

        $this->assertEquals(1024, $mediaFile->getContentLength());
        $this->assertEquals('text/plain', $mediaFile->getMimeType());
        $this->assertEquals(hash('crc32', $content), $mediaFile->getHash());
    }

    public function testMediaFilePathHandling()
    {
        $this->file('_media/subfolder/nested_file.txt', 'Nested content');

        $mediaFile = MediaFile::make('subfolder/nested_file.txt');

        $this->assertEquals('subfolder/nested_file.txt', $mediaFile->getIdentifier());
        $this->assertEquals('_media/subfolder/nested_file.txt', $mediaFile->getPath());
    }

    public function testMediaFileExceptionHandling()
    {
        $this->expectException(FileNotFoundException::class);
        MediaFile::make('non_existent_file.txt');
    }

    public function testMediaDirectoryCustomization()
    {
        Hyde::setMediaDirectory('custom_media');

        $this->file('custom_media/custom_file.txt', 'Custom content');

        $mediaFile = MediaFile::make('custom_file.txt');

        $this->assertEquals('custom_media/custom_file.txt', $mediaFile->getPath());
        $this->assertEquals(Hyde::path('custom_media/custom_file.txt'), $mediaFile->getAbsolutePath());

        Hyde::setMediaDirectory('_media');
    }

    public function testMediaFileOutputPaths()
    {
        $this->assertEquals(Hyde::path('_site/media'), MediaFile::outputPath());
        $this->assertEquals(Hyde::path('_site/media/test.css'), MediaFile::outputPath('test.css'));

        Hyde::setOutputDirectory('custom_output');
        $this->assertEquals(Hyde::path('custom_output/media'), MediaFile::outputPath());

        Hyde::setOutputDirectory('_site');
    }

    public function testMediaFileCacheBusting()
    {
        $this->file('_media/cachebust_test.js', 'console.log("Hello");');

        $cacheBustKey = MediaFile::getCacheBustKey('cachebust_test.js');

        $this->assertStringStartsWith('?v=', $cacheBustKey);
        $this->assertSame('?v=cd5de5e7', $cacheBustKey); // Expect CRC32 hash
    }
}
