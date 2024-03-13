<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Foundation\HydeKernel;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Metadata\PageMetadataBag;
use Hyde\Framework\Testing\helpers\BaseHydePageUnitTest;

require_once __DIR__.'/../../helpers/BaseHydePageUnitTest.php';

/**
 * @covers \Hyde\Pages\InMemoryPage
 *
 * @see \Hyde\Framework\Testing\Unit\Pages\InMemoryPageTest
 */
class InMemoryPageUnitTest extends BaseHydePageUnitTest
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '',
            InMemoryPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            '',
            InMemoryPage::outputDirectory()
        );
    }

    public function testBaseRouteKey()
    {
        $this->assertSame(
            '',
            InMemoryPage::baseRouteKey()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '',
            InMemoryPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'hello-world',
            InMemoryPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            InMemoryPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('hello-world'),
            InMemoryPage::path('hello-world')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'hello-world',
            (new InMemoryPage('hello-world'))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            (new InMemoryPage('hello-world'))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'hello-world.html',
            (new InMemoryPage('hello-world'))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(InMemoryPage::make('foo'), new InMemoryPage('foo'));
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            InMemoryPage::make('foo', ['foo' => 'bar']),
            new InMemoryPage('foo', matter: ['foo' => 'bar'])
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new InMemoryPage('foo'))->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new InMemoryPage('foo'))->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new InMemoryPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new InMemoryPage('foo'))->navigationMenuGroup());
    }

    public function testGetBladeView()
    {
        $this->assertSame('foo', (new InMemoryPage('foo', view: 'foo'))->getBladeView());
    }

    public function testFiles()
    {
        $this->assertSame([], InMemoryPage::files());
    }

    public function testData()
    {
        $this->assertSame('foo', (new InMemoryPage('foo'))->data('identifier'));
    }

    public function testGet()
    {
        $page = new InMemoryPage('foo');
        HydeKernel::getInstance()->pages()->put('foo', $page);
        $this->assertSame($page, InMemoryPage::get('foo'));
    }

    public function testParse()
    {
        $this->file(InMemoryPage::sourcePath('foo'));
        $this->assertInstanceOf(InMemoryPage::class, InMemoryPage::parse('foo'));
    }

    public function testGetRouteKey()
    {
        $this->assertSame('foo', (new InMemoryPage('foo'))->getRouteKey());
    }

    public function testTitle()
    {
        $inMemoryPage = new InMemoryPage('foo');
        $this->assertSame('HydePHP - Foo', $inMemoryPage->title());
    }

    public function testAll()
    {
        $this->assertInstanceOf(PageCollection::class, InMemoryPage::all());
    }

    public function testMetadata()
    {
        $this->assertInstanceOf(PageMetadataBag::class, (new InMemoryPage('foo'))->metadata());
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(InMemoryPage::class, new InMemoryPage('foo'));
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf(Route::class, (new InMemoryPage('foo'))->getRoute());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('foo', (new InMemoryPage('foo'))->getIdentifier());
    }

    public function testHas()
    {
        $this->assertTrue((new InMemoryPage('foo'))->has('identifier'));
    }

    public function testToCoreDataObject()
    {
        $this->assertInstanceOf(CoreDataObject::class, (new InMemoryPage('foo'))->toCoreDataObject());
    }

    public function testCompile()
    {
        $this->file('_pages/foo.html');

        $page = new InMemoryPage('foo');
        Hyde::shareViewData($page);
        $this->assertIsString(InMemoryPage::class, $page->compile());
    }

    public function testMatter()
    {
        $this->assertInstanceOf(FrontMatter::class, (new InMemoryPage('404'))->matter());
    }
}
