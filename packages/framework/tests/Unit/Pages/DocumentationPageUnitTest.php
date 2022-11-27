<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\DocumentationPage
 */
class DocumentationPageUnitTest extends TestCase
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '_docs',
            DocumentationPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            'docs',
            DocumentationPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            DocumentationPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            '_docs/hello-world.md',
            DocumentationPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'docs/hello-world.html',
            DocumentationPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('_docs/hello-world.md'),
            DocumentationPage::path('hello-world.md')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            '_docs/hello-world.md',
            (new DocumentationPage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'docs/hello-world.html',
            (new DocumentationPage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'docs/hello-world.html',
            (new DocumentationPage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(DocumentationPage::make(), new DocumentationPage());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            DocumentationPage::make('foo', ['foo' => 'bar']),
            new DocumentationPage('foo', ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertFalse((new DocumentationPage('foo'))->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new DocumentationPage('foo'))->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new DocumentationPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new DocumentationPage('foo'))->navigationMenuGroup());
    }
}
