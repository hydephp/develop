<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Support;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Support\Filesystem\ProjectFile;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\ProjectFile
 */
class ProjectFileTest extends TestCase
{
    public function testCanConstruct()
    {
        $file = new ProjectFileTestClass('foo');
        $this->assertInstanceOf(ProjectFileTestClass::class, $file);
        $this->assertSame('foo', $file->path);
    }

    public function can_make()
    {
        $this->assertEquals(new ProjectFileTestClass('foo'), ProjectFileTestClass::make('foo'));
    }

    public function testCanConstructWithNestedPaths()
    {
        $this->assertEquals('path/to/file.txt', ProjectFileTestClass::make('path/to/file.txt')->path);
    }

    public function testAbsolutePathIsNormalizedToRelative()
    {
        $this->assertEquals('foo', ProjectFileTestClass::make(Hyde::path('foo'))->path);
    }

    public function testGetNameReturnsNameOfFile()
    {
        $this->assertSame('foo.txt', ProjectFileTestClass::make('foo.txt')->getName());
        $this->assertSame('bar.txt', ProjectFileTestClass::make('foo/bar.txt')->getName());
    }

    public function testGetPathReturnsPathOfFile()
    {
        $this->assertSame('foo.txt', ProjectFileTestClass::make('foo.txt')->getPath());
        $this->assertSame('foo/bar.txt', ProjectFileTestClass::make('foo/bar.txt')->getPath());
    }

    public function testGetAbsolutePathReturnsAbsolutePathOfFile()
    {
        $this->assertSame(Hyde::path('foo.txt'), ProjectFileTestClass::make('foo.txt')->getAbsolutePath());
        $this->assertSame(Hyde::path('foo/bar.txt'), ProjectFileTestClass::make('foo/bar.txt')->getAbsolutePath());
    }

    public function testGetContentsReturnsContentsOfFile()
    {
        $this->file('foo.txt', 'foo bar');
        $this->assertSame('foo bar', ProjectFileTestClass::make('foo.txt')->getContents());
    }

    public function testGetExtensionReturnsExtensionOfFile()
    {
        $this->file('foo.txt', 'foo');
        $this->assertSame('txt', ProjectFileTestClass::make('foo.txt')->getExtension());

        $this->file('foo.png', 'foo');
        $this->assertSame('png', ProjectFileTestClass::make('foo.png')->getExtension());
    }

    public function testToArrayReturnsArrayOfFileProperties()
    {
        $this->file('foo.txt', 'foo bar');

        $this->assertSame([
            'name' => 'foo.txt',
            'path' => 'foo.txt',
        ], ProjectFileTestClass::make('foo.txt')->toArray());
    }

    public function testToArrayWithEmptyFileWithNoExtension()
    {
        $this->file('foo');
        $this->assertSame([
            'name' => 'foo',
            'path' => 'foo',
        ], ProjectFileTestClass::make('foo')->toArray());
    }

    public function testToArrayWithFileInSubdirectory()
    {
        mkdir(Hyde::path('foo'));
        touch(Hyde::path('foo/bar.txt'));
        $this->assertSame([
            'name' => 'bar.txt',
            'path' => 'foo/bar.txt',
        ], ProjectFileTestClass::make('foo/bar.txt')->toArray());
        Filesystem::unlink('foo/bar.txt');
        rmdir(Hyde::path('foo'));
    }
}

class ProjectFileTestClass extends ProjectFile
{
    //
}
