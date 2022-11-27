<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\MarkdownPage
 */
class MarkdownPageUnitTest extends TestCase
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '_pages',
            MarkdownPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            '',
            MarkdownPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            MarkdownPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            '_pages/hello-world.md',
            MarkdownPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            MarkdownPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('_pages/hello-world.md'),
            MarkdownPage::path('hello-world.md')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            '_pages/hello-world.md',
            (new MarkdownPage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            (new MarkdownPage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'hello-world.html',
            (new MarkdownPage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(MarkdownPage::make(), new MarkdownPage());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            MarkdownPage::make('foo', ['foo' => 'bar']),
            new MarkdownPage('foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new MarkdownPage('foo'))->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new MarkdownPage('foo'))->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new MarkdownPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new MarkdownPage('foo'))->navigationMenuGroup());
    }
}
