<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\HtmlPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\HtmlPage
 */
class HtmlPageUnitTest extends TestCase
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '_pages',
            HtmlPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            '',
            HtmlPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.html',
            HtmlPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            '_pages/hello-world.html',
            HtmlPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            HtmlPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('_pages/hello-world.html'),
            HtmlPage::path('hello-world.html')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            '_pages/hello-world.html',
            (new HtmlPage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            (new HtmlPage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'hello-world.html',
            (new HtmlPage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(HtmlPage::make(), new HtmlPage());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            HtmlPage::make('foo', ['foo' => 'bar']),
            new HtmlPage('foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new HtmlPage('foo'))->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new HtmlPage('foo'))->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new HtmlPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new HtmlPage('foo'))->navigationMenuGroup());
    }
}
