<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Support\Filesystem\RelativePathString;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Filesystem\RelativePathString
 */
class RelativePathStringTest extends TestCase
{
    public function testCanCreateClassUsingConstructor()
    {
        $this->assertInstanceOf(RelativePathString::class, RelativePathString::make('foo'));
    }

    public function testCanCreateClassUsingStaticMakeMethod()
    {
        $this->assertInstanceOf(RelativePathString::class, RelativePathString::make('foo'));
        $this->assertEquals(new RelativePathString('foo'), RelativePathString::make('foo'));
    }

    public function testCanGetPathValue()
    {
        $this->assertSame('foo', RelativePathString::make('foo')->getValue());
    }

    public function testCanCastToString()
    {
        $this->assertEquals('foo', (string) RelativePathString::make('foo'));
    }

    public function testCanCastToArray()
    {
        $this->assertEquals(['relative_path' => 'foo'], RelativePathString::make('foo')->toArray());
    }

    public function testAbsolutePathIsCastToRelative()
    {
        $this->assertEquals('foo', RelativePathString::make(Hyde::path('foo')));
    }
}
