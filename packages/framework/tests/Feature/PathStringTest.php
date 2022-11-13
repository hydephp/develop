<?php

declare(strict_types=1);


use Hyde\Hyde;
use Hyde\Support\Filesystem\AbsolutePathString;
use Hyde\Support\Filesystem\RelativePathString;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\RelativePathString
 * @covers \Hyde\Support\Filesystem\PathString
 */
class PathStringTest extends TestCase
{
    public function testCanCreateRelativePathStringClassUsingConstructor()
    {
        $this->assertInstanceOf(RelativePathString::class, new RelativePathString('foo'));
    }

    public function testCanCreateRelativePathStringClassUsingStaticMakeMethod()
    {
        $this->assertInstanceOf(RelativePathString::class, RelativePathString::make('foo'));
        $this->assertEquals(new RelativePathString('foo'), RelativePathString::make('foo'));
    }

    public function testCanGetRelativePathStringPathValue()
    {
        $this->assertSame('foo', RelativePathString::make('foo')->getValue());
    }

    public function testCanCastRelativePathStringToString()
    {
        $this->assertEquals('foo', (string) RelativePathString::make('foo'));
    }

    public function testCanCastRelativePathStringToArray()
    {
        $this->assertEquals(['relative_path' => 'foo'], RelativePathString::make('foo')->toArray());
    }

    public function testRelativePathStringCastsAbsolutePathToRelative()
    {
        $this->assertEquals('foo', RelativePathString::make(Hyde::path('foo')));
    }

    public function testCanCreateAbsolutePathStringClassUsingConstructor()
    {
        $this->assertInstanceOf(AbsolutePathString::class, new AbsolutePathString('foo'));
    }

    public function testCanCreateAbsolutePathStringClassUsingStaticMakeMethod()
    {
        $this->assertInstanceOf(AbsolutePathString::class, AbsolutePathString::make('foo'));
        $this->assertEquals(new AbsolutePathString('foo'), AbsolutePathString::make('foo'));
    }

    public function testCanGetAbsolutePathStringPathValue()
    {
        $this->assertSame(Hyde::path('foo'), AbsolutePathString::make('foo')->getValue());
    }

    public function testCanCastAbsolutePathStringToString()
    {
        $this->assertEquals(Hyde::path('foo'), (string) AbsolutePathString::make('foo'));
    }

    public function testCanCastAbsolutePathStringToArray()
    {
        $this->assertEquals(['absolute_path' => Hyde::path('foo')], AbsolutePathString::make('foo')->toArray());
    }

    public function testAbsolutePathStringNormalizesAlreadyAbsolutePaths()
    {
        $this->assertEquals(Hyde::path('foo'), AbsolutePathString::make(Hyde::path('foo')));
    }
}
