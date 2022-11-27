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
use Hyde\Pages\DocumentationPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

require_once __DIR__.'/BaseHydePageUnitTest.php';

/**
 * @covers \Hyde\Pages\DocumentationPage
 */
class DocumentationPageUnitTest extends BaseHydePageUnitTest
{
    public function testSourceDirectory()
    {
        $this->assertSame('_docs', DocumentationPage::sourceDirectory());
    }

    public function testOutputDirectory()
    {
        $this->assertSame('docs', DocumentationPage::outputDirectory());
    }

    public function testFileExtension()
    {
        $this->assertSame('.md', DocumentationPage::fileExtension());
    }

    public function testSourcePath()
    {
        $this->assertSame('_docs/hello-world.md', DocumentationPage::sourcePath('hello-world'));
    }

    public function testOutputPath()
    {
        $this->assertSame('docs/hello-world.html', DocumentationPage::outputPath('hello-world'));
    }

    public function testPath()
    {
        $this->assertSame(Hyde::path('_docs/hello-world.md'), DocumentationPage::path('hello-world.md'));
    }

    public function testGetSourcePath()
    {
        $this->assertSame('_docs/hello-world.md', (new DocumentationPage('hello-world'))->getSourcePath());
    }

    public function testGetOutputPath()
    {
        $this->assertSame('docs/hello-world.html', (new DocumentationPage('hello-world'))->getOutputPath());
    }

    public function testGetLink()
    {
        $this->assertSame('docs/hello-world.html', (new DocumentationPage('hello-world'))->getLink());
    }

    public function testMake()
    {
        $this->assertEquals(DocumentationPage::make(), new DocumentationPage());
    }

    public function testMakeWithData()
    {
        $this->assertEquals(DocumentationPage::make('foo', ['foo' => 'bar']), new DocumentationPage('foo', ['foo' => 'bar']));
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new DocumentationPage())->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new DocumentationPage())->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new DocumentationPage('foo'))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertSame('other', (new DocumentationPage('foo'))->navigationMenuGroup());
    }

    public function testNavigationMenuGroupWithData()
    {
        $this->assertSame('foo', DocumentationPage::make(matter: ['navigation' => ['group' => 'foo']])->navigationMenuGroup());
    }

    public function testGetBladeView()
    {
        $this->assertSame('hyde::layouts/docs', (new DocumentationPage('foo'))->getBladeView());
    }

    public function testFiles()
    {
        $this->assertSame([], DocumentationPage::files());
    }

    public function testData()
    {
        $this->assertSame('foo', (new DocumentationPage('foo'))->data('identifier'));
    }

    public function testGet()
    {
        $this->file(DocumentationPage::sourcePath('foo'));
        $this->assertEquals(new DocumentationPage('foo'), DocumentationPage::get('foo'));
    }

    public function testParse()
    {
        $this->file(DocumentationPage::sourcePath('foo'));
        $this->assertInstanceOf(DocumentationPage::class, DocumentationPage::parse('foo'));
    }

    public function testGetRouteKey()
    {
        $this->assertSame('docs/foo', (new DocumentationPage('foo'))->getRouteKey());
    }

    public function testHtmlTitle()
    {
        $this->assertSame('HydePHP - Foo', (new DocumentationPage('foo'))->htmlTitle());
    }

    public function testAll()
    {
        $this->assertInstanceOf(PageCollection::class, DocumentationPage::all());
    }

    public function testMetadata()
    {
        $this->assertInstanceOf(PageMetadataBag::class, (new DocumentationPage())->metadata());
    }

    public function test__construct()
    {
        $this->assertInstanceOf(DocumentationPage::class, new DocumentationPage());
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf(Route::class, (new DocumentationPage())->getRoute());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('foo', (new DocumentationPage('foo'))->getIdentifier());
    }

    public function testHas()
    {
        $this->assertTrue((new DocumentationPage('foo'))->has('identifier'));
    }

    public function testToCoreDataObject()
    {
        $this->assertInstanceOf(CoreDataObject::class, (new DocumentationPage('foo'))->toCoreDataObject());
    }

    public function testConstructFactoryData()
    {
        (new DocumentationPage())->constructFactoryData($this->mockPageDataFactory());
        $this->assertTrue(true);
    }

    public function testCompile()
    {
        $page = new DocumentationPage('foo');
        Hyde::shareViewData($page);
        $this->assertIsString(DocumentationPage::class, $page->compile());
    }

    public function testMatter()
    {
        $this->assertInstanceOf(FrontMatter::class, (new DocumentationPage('foo'))->matter());
    }

    protected function mockPageDataFactory(): PageDataFactory
    {
        return new HydePageDataFactory(new CoreDataObject(new FrontMatter(), false, '', '', '', '', ''));
    }
}
