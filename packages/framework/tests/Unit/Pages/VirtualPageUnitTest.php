<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\PageCollection;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Metadata\PageMetadataBag;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\VirtualPage;
use Hyde\Support\Models\Route;

require_once __DIR__.'/BaseHydePageUnitTest.php';

/**
 * @covers \Hyde\Pages\VirtualPage
 *
 * @see \Hyde\Framework\Testing\Unit\VirtualPageTest
 */
class VirtualPageUnitTest extends BaseHydePageUnitTest
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '',
            VirtualPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            '',
            VirtualPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '',
            VirtualPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'hello-world',
            VirtualPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            VirtualPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('hello-world'),
            VirtualPage::path('hello-world')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'hello-world',
            (new VirtualPage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            (new VirtualPage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'hello-world.html',
            (new VirtualPage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(VirtualPage::make('foo'), new VirtualPage('foo'));
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            VirtualPage::make('foo', ['foo' => 'bar']),
            new VirtualPage('foo', matter: ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new VirtualPage('foo'))->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new VirtualPage('foo'))->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new VirtualPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new VirtualPage('foo'))->navigationMenuGroup());
    }

    public function testGetBladeView()
    {
        $this->assertSame('foo', (new VirtualPage('foo', view: 'foo'))->getBladeView());
    }

    public function testFiles()
    {
        $this->assertSame([], VirtualPage::files());
    }

    public function testData()
    {
        $this->assertSame('foo', (new VirtualPage('foo'))->data('identifier'));
    }

    public function testGet()
    {
        $page = new VirtualPage('foo');
        HydeKernel::getInstance()->pages()->put('foo', $page);
        $this->assertSame($page, VirtualPage::get('foo'));
    }

    public function testParse()
    {
        $this->file(VirtualPage::sourcePath('foo'));
        $this->assertInstanceOf(VirtualPage::class, VirtualPage::parse('foo'));
    }

    public function testGetRouteKey()
    {
        $this->assertSame('foo', (new VirtualPage('foo'))->getRouteKey());
    }

    public function testHtmlTitle()
    {
        $this->assertSame('HydePHP - Foo', (new VirtualPage('foo'))->htmlTitle());
    }

    public function testAll()
    {
        $this->assertInstanceOf(PageCollection::class, VirtualPage::all());
    }

    public function testMetadata()
    {
        $this->assertInstanceOf(PageMetadataBag::class, (new VirtualPage('foo'))->metadata());
    }

    public function test__construct()
    {
        $this->assertInstanceOf(VirtualPage::class, new VirtualPage('foo'));
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf(Route::class, (new VirtualPage('foo'))->getRoute());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('foo', (new VirtualPage('foo'))->getIdentifier());
    }

    public function testHas()
    {
        $this->assertTrue((new VirtualPage('foo'))->has('identifier'));
    }

    public function testToCoreDataObject()
    {
        $this->assertInstanceOf(CoreDataObject::class, (new VirtualPage('foo'))->toCoreDataObject());
    }

    public function testConstructFactoryData()
    {
        (new VirtualPage('foo'))->constructFactoryData($this->mockPageDataFactory());
        $this->assertTrue(true);
    }

    public function testCompile()
    {
        $this->file('_pages/foo.html');

        $page = new VirtualPage('foo');
        Hyde::shareViewData($page);
        $this->assertIsString(VirtualPage::class, $page->compile());
    }

    public function testMatter()
    {
        $this->assertInstanceOf(FrontMatter::class, (new VirtualPage('404'))->matter());
    }
}
