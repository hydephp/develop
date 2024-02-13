<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\SourceFile
 */
class SourceFileTest extends TestCase
{
    public function testCanConstruct()
    {
        $file = new SourceFile('foo');
        $this->assertInstanceOf(SourceFile::class, $file);

        $this->assertSame('foo', $file->path);
        $this->assertSame(HydePage::class, $file->pageClass);
    }

    public function testCanConstructWithModelClass()
    {
        $file = new SourceFile('foo', MarkdownPage::class);
        $this->assertInstanceOf(SourceFile::class, $file);

        $this->assertSame('foo', $file->path);
        $this->assertSame(MarkdownPage::class, $file->pageClass);
    }

    public function can_make()
    {
        $this->assertEquals(new SourceFile('foo'), SourceFile::make('foo'));
    }

    public function testCanMakeWithModelClass()
    {
        $this->assertEquals(new SourceFile('foo', MarkdownPage::class),
            SourceFile::make('foo', MarkdownPage::class));
    }

    public function testCanConstructWithNestedPaths()
    {
        $this->assertEquals('path/to/file.txt', SourceFile::make('path/to/file.txt')->path);
    }

    public function testAbsolutePathIsNormalizedToRelative()
    {
        $this->assertEquals('foo', SourceFile::make(Hyde::path('foo'))->path);
    }

    public function testGetNameReturnsNameOfFile()
    {
        $this->assertSame('foo.txt', SourceFile::make('foo.txt')->getName());
        $this->assertSame('bar.txt', SourceFile::make('foo/bar.txt')->getName());
    }

    public function testGetPathReturnsPathOfFile()
    {
        $this->assertSame('foo.txt', SourceFile::make('foo.txt')->getPath());
        $this->assertSame('foo/bar.txt', SourceFile::make('foo/bar.txt')->getPath());
    }

    public function testGetAbsolutePathReturnsAbsolutePathOfFile()
    {
        $this->assertSame(Hyde::path('foo.txt'), SourceFile::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('foo/bar.txt'), SourceFile::make('foo/bar.txt')->getAbsolutePath());
    }

    public function testGetContentsReturnsContentsOfFile()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame('foo bar', SourceFile::make('foo.txt')->getContents());
    }

    public function testGetExtensionReturnsExtensionOfFile()
    {
        $this->file('foo.txt', 'foo');
        $this->assertSame('txt', SourceFile::make('foo.txt')->getExtension());

        $this->file('foo.png', 'foo');
        $this->assertSame('png', SourceFile::make('foo.png')->getExtension());
    }

    public function testToArrayReturnsArrayOfFileProperties()
    {
        $this->file('foo.txt', 'foo bar');

        $this->assertSame([
            'name' => 'foo.txt',
            'path' => 'foo.txt',
            'pageClass' => HydePage::class,
        ], SourceFile::make('foo.txt')->toArray());
    }

    public function testToArrayWithEmptyFileWithNoExtension()
    {
        $this->file('foo');
        $this->assertSame([
            'name' => 'foo',
            'path' => 'foo',
            'pageClass' => HydePage::class,
        ], SourceFile::make('foo')->toArray());
    }

    public function testToArrayWithFileInSubdirectory()
    {
        mkdir(Hyde::path('foo'));
        touch(Hyde::path('foo/bar.txt'));
        $this->assertSame([
            'name' => 'bar.txt',
            'path' => 'foo/bar.txt',
            'pageClass' => HydePage::class,
        ], SourceFile::make('foo/bar.txt')->toArray());
        Filesystem::unlink('foo/bar.txt');
        rmdir(Hyde::path('foo'));
    }
}
