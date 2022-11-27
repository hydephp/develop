<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\BladePage
 */
class BladePageUnitTest extends TestCase implements BaseHydePageUnitTestMethods
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '_pages',
            BladePage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            '',
            BladePage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.blade.php',
            BladePage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            '_pages/hello-world.blade.php',
            BladePage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            BladePage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('_pages/hello-world.blade.php'),
            BladePage::path('hello-world.blade.php')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            '_pages/hello-world.blade.php',
            (new BladePage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            (new BladePage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'hello-world.html',
            (new BladePage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(BladePage::make(), new BladePage());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            BladePage::make('foo', ['foo' => 'bar']),
            new BladePage('foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new BladePage())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new BladePage())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new BladePage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new BladePage('foo'))->navigationMenuGroup());
    }
}
