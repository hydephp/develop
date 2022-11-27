<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Foundation\PageCollection;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Factories\Concerns\PageDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Framework\Features\Metadata\PageMetadataBag;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\HtmlPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

require_once __DIR__.'/BaseHydePageUnitTestMethods.php';

/**
 * @covers \Hyde\Pages\HtmlPage
 */
class HtmlPageUnitTest extends TestCase implements BaseHydePageUnitTestMethods
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
        $this->assertTrue((new HtmlPage())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new HtmlPage())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new HtmlPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new HtmlPage('foo'))->navigationMenuGroup());
    }

    public function testGetBladeView()
    {
        $this->assertSame('hyde::layouts/html', (new HtmlPage('foo'))->getBladeView());
    }

    public function testFiles()
    {
        $this->assertSame([], HtmlPage::files());
    }

    public function testData()
    {
        $this->assertSame('foo', (new HtmlPage('foo'))->data('identifier'));
    }

    public function testGet()
    {
        $this->file(HtmlPage::sourcePath('foo'));
        $this->assertEquals(new HtmlPage('foo'), HtmlPage::get('foo'));
    }

    public function testParse()
    {
        $this->file(HtmlPage::sourcePath('foo'));
        $this->assertInstanceOf(HtmlPage::class, HtmlPage::parse('foo'));
    }

    public function testGetRouteKey()
    {
        $this->assertSame('foo', (new HtmlPage('foo'))->getRouteKey());
    }

    public function testHtmlTitle()
    {
        $this->assertSame('HydePHP - Foo', (new HtmlPage('foo'))->htmlTitle());
    }

    public function testAll()
    {
        $this->assertInstanceOf(PageCollection::class, HtmlPage::all());
    }

    public function testMetadata()
    {
        $this->assertInstanceOf(PageMetadataBag::class, (new HtmlPage())->metadata());
    }

    public function test__construct()
    {
        $this->assertInstanceOf(HtmlPage::class, new HtmlPage());
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf(Route::class, (new HtmlPage())->getRoute());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('foo', (new HtmlPage('foo'))->getIdentifier());
    }

    public function testHas()
    {
        $this->assertTrue((new HtmlPage('foo'))->has('identifier'));
    }

    public function testToCoreDataObject()
    {
        $this->assertInstanceOf(CoreDataObject::class, (new HtmlPage('foo'))->toCoreDataObject());
    }

    public function testConstructFactoryData()
    {
        (new HtmlPage())->constructFactoryData($this->mockPageDataFactory());
        $this->assertTrue(true);
    }

    public function testCompile()
    {
        $page = new HtmlPage('404');
        Hyde::shareViewData($page);
        $this->assertIsString(HtmlPage::class, $page->compile());
    }

    public function testMatter()
    {
        $this->assertInstanceOf(FrontMatter::class, (new HtmlPage('404'))->matter());
    }

    protected function mockPageDataFactory(): PageDataFactory
    {
        return new HydePageDataFactory(new CoreDataObject(new FrontMatter(), false, '', '', '', '', ''));
    }
}
