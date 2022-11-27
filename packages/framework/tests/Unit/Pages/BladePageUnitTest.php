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
use Hyde\Pages\BladePage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

require_once __DIR__.'/BaseHydePageUnitTestMethods.php';

/**
 * @covers \Hyde\Pages\BladePage
 */
class BladePageUnitTest extends TestCase implements BaseHydePageUnitTestMethods
{
    public function testSourceDirectory()
    {
        $this->assertSame('_pages', BladePage::sourceDirectory());
    }

    public function testOutputDirectory()
    {
        $this->assertSame('', BladePage::outputDirectory());
    }

    public function testFileExtension()
    {
        $this->assertSame('.blade.php', BladePage::fileExtension());
    }

    public function testSourcePath()
    {
        $this->assertSame('_pages/hello-world.blade.php', BladePage::sourcePath('hello-world'));
    }

    public function testOutputPath()
    {
        $this->assertSame('hello-world.html', BladePage::outputPath('hello-world'));
    }

    public function testPath()
    {
        $this->assertSame(Hyde::path('_pages/hello-world.blade.php'), BladePage::path('hello-world.blade.php'));
    }

    public function testGetSourcePath()
    {
        $this->assertSame('_pages/hello-world.blade.php', (new BladePage('hello-world'))->getSourcePath());
    }

    public function testGetOutputPath()
    {
        $this->assertSame('hello-world.html', (new BladePage('hello-world'))->getOutputPath());
    }

    public function testGetLink()
    {
        $this->assertSame('hello-world.html', (new BladePage('hello-world'))->getLink());
    }

    public function testMake()
    {
        $this->assertEquals(BladePage::make(), new BladePage());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(BladePage::make('foo', ['foo' => 'bar']), new BladePage('foo', ['foo' => 'bar']));
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

    public function testGetBladeView()
    {
        $this->assertSame('foo', (new BladePage('foo'))->getBladeView());
    }

    public function testFiles()
    {
        $this->assertSame(['404', 'index'], BladePage::files());
    }

    public function testData()
    {
        $this->assertSame('foo', (new BladePage('foo'))->data('identifier'));
    }

    public function testGet()
    {
        $this->file(BladePage::sourcePath('foo'));
        $this->assertEquals(new BladePage('foo'), BladePage::get('foo'));
    }

    public function testParse()
    {
        $this->assertInstanceOf(BladePage::class, BladePage::parse('404'));
    }

    public function testGetRouteKey()
    {
        $this->assertSame('foo', (new BladePage('foo'))->getRouteKey());
    }

    public function testHtmlTitle()
    {
        $this->assertSame('HydePHP - Foo', (new BladePage('foo'))->htmlTitle());
    }

    public function testAll()
    {
        $this->assertInstanceOf(PageCollection::class, BladePage::all());
    }

    public function testMetadata()
    {
        $this->assertInstanceOf(PageMetadataBag::class, (new BladePage())->metadata());
    }

    public function test__construct()
    {
        $this->assertInstanceOf(BladePage::class, new BladePage());
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf(Route::class, (new BladePage())->getRoute());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('foo', (new BladePage('foo'))->getIdentifier());
    }

    public function testHas()
    {
        $this->assertTrue((new BladePage('foo'))->has('identifier'));
    }

    public function testToCoreDataObject()
    {
        $this->assertInstanceOf(CoreDataObject::class, (new BladePage('foo'))->toCoreDataObject());
    }

    public function testConstructFactoryData()
    {
        (new BladePage())->constructFactoryData($this->mockPageDataFactory());
        $this->assertTrue(true);
    }

    public function testCompile()
    {
        $this->assertIsString(BladePage::class, (new BladePage('404'))->compile());
    }

    public function testMatter()
    {
        $this->assertInstanceOf(FrontMatter::class, (new BladePage('404'))->matter());
    }

    protected function mockPageDataFactory(): PageDataFactory
    {
        return new HydePageDataFactory(new CoreDataObject(new FrontMatter(), false, '', '', '', '', ''));
    }
}
