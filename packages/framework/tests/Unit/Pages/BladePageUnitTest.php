<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Framework\Testing\Feature\TestPage;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\BladePage
 */
class BladePageUnitTest extends TestCase
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            'source',
            TestPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            'output',
            TestPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            TestPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            TestPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            TestPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('source/hello-world'),
            TestPage::path('hello-world')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'source/hello-world.md',
            (new TestPage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'output/hello-world.html',
            (new TestPage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'output/hello-world.html',
            (new TestPage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(TestPage::make(), new TestPage());

        $this->assertEquals(
            TestPage::make('foo', ['foo' => 'bar']),
            new TestPage('foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new BladePage('foo'))->showInNavigation());
        $this->assertTrue((new MarkdownPage())->showInNavigation());
        $this->assertTrue((new DocumentationPage())->showInNavigation());
        $this->assertFalse((new MarkdownPost())->showInNavigation());
        $this->assertTrue((new HtmlPage())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new BladePage('foo'))->navigationMenuPriority());
        $this->assertSame(999, (new MarkdownPage())->navigationMenuPriority());
        $this->assertSame(999, (new DocumentationPage())->navigationMenuPriority());
        $this->assertSame(10, (new MarkdownPost())->navigationMenuPriority());
        $this->assertSame(999, (new HtmlPage())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new BladePage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new MarkdownPage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new MarkdownPost('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new DocumentationPage('foo'))->navigationMenuLabel());
        $this->assertSame('Foo', (new HtmlPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new BladePage('foo'))->navigationMenuGroup());
        $this->assertNull((new MarkdownPage())->navigationMenuGroup());
        $this->assertNull((new MarkdownPost())->navigationMenuGroup());
        $this->assertNull((new HtmlPage())->navigationMenuGroup());
        $this->assertSame('other', (new DocumentationPage())->navigationMenuGroup());
        $this->assertSame('foo', DocumentationPage::make(matter: ['navigation' => ['group' => 'foo']])->navigationMenuGroup());
    }
}
