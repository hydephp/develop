<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Foundation\PageCollection;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Metadata\PageMetadataBag;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\MarkdownPage;
use Hyde\Support\Models\Route;
use function unlink;

require_once __DIR__.'/BaseMarkdownPageUnitTest.php';

/**
 * @covers \Hyde\Pages\MarkdownPage
 */
class MarkdownPageUnitTest extends BaseMarkdownPageUnitTest
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
        $this->assertTrue((new MarkdownPage())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new MarkdownPage())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new MarkdownPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new MarkdownPage('foo'))->navigationMenuGroup());
    }

    public function testGetBladeView()
    {
        $this->assertSame('hyde::layouts/page', (new MarkdownPage('foo'))->getBladeView());
    }

    public function testFiles()
    {
        $this->assertSame([], MarkdownPage::files());
    }

    public function testData()
    {
        $this->assertSame('foo', (new MarkdownPage('foo'))->data('identifier'));
    }

    public function testGet()
    {
        $this->file(MarkdownPage::sourcePath('foo'));
        $this->assertEquals(new MarkdownPage('foo'), MarkdownPage::get('foo'));
    }

    public function testParse()
    {
        $this->file(MarkdownPage::sourcePath('foo'));
        $this->assertInstanceOf(MarkdownPage::class, MarkdownPage::parse('foo'));
    }

    public function testGetRouteKey()
    {
        $this->assertSame('foo', (new MarkdownPage('foo'))->getRouteKey());
    }

    public function testHtmlTitle()
    {
        $this->assertSame('HydePHP - Foo', (new MarkdownPage('foo'))->htmlTitle());
    }

    public function testAll()
    {
        $this->assertInstanceOf(PageCollection::class, MarkdownPage::all());
    }

    public function testMetadata()
    {
        $this->assertInstanceOf(PageMetadataBag::class, (new MarkdownPage())->metadata());
    }

    public function test__construct()
    {
        $this->assertInstanceOf(MarkdownPage::class, new MarkdownPage());
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf(Route::class, (new MarkdownPage())->getRoute());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('foo', (new MarkdownPage('foo'))->getIdentifier());
    }

    public function testHas()
    {
        $this->assertTrue((new MarkdownPage('foo'))->has('identifier'));
    }

    public function testToCoreDataObject()
    {
        $this->assertInstanceOf(CoreDataObject::class, (new MarkdownPage('foo'))->toCoreDataObject());
    }

    public function testConstructFactoryData()
    {
        (new MarkdownPage())->constructFactoryData($this->mockPageDataFactory());
        $this->assertTrue(true);
    }

    public function testCompile()
    {
        $page = new MarkdownPage('foo');
        Hyde::shareViewData($page);
        $this->assertIsString(MarkdownPage::class, $page->compile());
    }

    public function testMatter()
    {
        $this->assertInstanceOf(FrontMatter::class, (new MarkdownPage('foo'))->matter());
    }

    public function testMarkdown()
    {
        $this->assertInstanceOf(Markdown::class, (new MarkdownPage('foo'))->markdown());
    }

    public function testSave()
    {
        $page = new MarkdownPage('foo');
        $this->assertSame($page, $page->save());
        $this->assertFileExists('_pages/foo.md');
        unlink(Hyde::path('_pages/foo.md'));
    }
}
