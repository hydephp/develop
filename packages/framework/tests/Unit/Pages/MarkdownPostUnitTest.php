<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\MarkdownPost
 */
class MarkdownPostUnitTest extends TestCase
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '_posts',
            MarkdownPost::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            'posts',
            MarkdownPost::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            MarkdownPost::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            '_posts/hello-world.md',
            MarkdownPost::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'posts/hello-world.html',
            MarkdownPost::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('_posts/hello-world.md'),
            MarkdownPost::path('hello-world.md')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            '_posts/hello-world.md',
            (new MarkdownPost('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'posts/hello-world.html',
            (new MarkdownPost('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'posts/hello-world.html',
            (new MarkdownPost('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(MarkdownPost::make(), new MarkdownPost());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            MarkdownPost::make('foo', ['foo' => 'bar']),
            new MarkdownPost('foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertFalse((new MarkdownPost())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new MarkdownPost())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new MarkdownPost('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new MarkdownPost('foo'))->navigationMenuGroup());
    }
}
