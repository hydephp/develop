<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Pages;

use Hyde\Markdown\Models\Markdown;
use function deleteDirectory;
use Hyde\Foundation\PageCollection;
use Hyde\Framework\Factories\Concerns\CoreDataObject;
use Hyde\Framework\Features\Metadata\PageMetadataBag;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\PublicationPage;
use Hyde\Support\Models\Route;

require_once __DIR__.'/BaseMarkdownPageUnitTest.php';

/**
 * @covers \Hyde\Pages\PublicationPage
 */
class PublicationPageUnitTest extends BaseMarkdownPageUnitTest
{
    public function testSourceDirectory()
    {
        $this->assertSame(
            '',
            PublicationPage::sourceDirectory()
        );
    }

    public function testOutputDirectory()
    {
        $this->assertSame(
            '',
            PublicationPage::outputDirectory()
        );
    }

    public function testFileExtension()
    {
        $this->assertSame(
            '.md',
            PublicationPage::fileExtension()
        );
    }

    public function testSourcePath()
    {
        $this->assertSame(
            'hello-world.md',
            PublicationPage::sourcePath('hello-world')
        );
    }

    public function testOutputPath()
    {
        $this->assertSame(
            'hello-world.html',
            PublicationPage::outputPath('hello-world')
        );
    }

    public function testPath()
    {
        $this->assertSame(
            Hyde::path('hello-world.md'),
            PublicationPage::path('hello-world.md')
        );
    }

    public function testGetSourcePath()
    {
        $this->assertSame(
            'directory/hello-world.md',
            (new PublicationPage('hello-world', [], '', $this->pubType()))->getSourcePath()
        );
    }

    public function testGetOutputPath()
    {
        $this->assertSame(
            'directory/hello-world.html',
            (new PublicationPage('hello-world', [], '', $this->pubType()))->getOutputPath()
        );
    }

    public function testGetLink()
    {
        $this->assertSame(
            'directory/hello-world.html',
            (new PublicationPage('hello-world', [], '', $this->pubType()))->getLink()
        );
    }

    public function testMake()
    {
        $this->assertEquals(PublicationPage::make(type: $this->pubType()), new PublicationPage('', [], '', $this->pubType()));
    }

    public function testMakeWithData()
    {
        $this->assertEquals(
            PublicationPage::make('foo', ['foo' => 'bar'], type: $this->pubType()),
            new PublicationPage('foo', ['foo' => 'bar'], '', $this->pubType())
        );
    }

    public function testShowInNavigation()
    {
        $this->assertTrue((new PublicationPage('', [], '', $this->pubType()))->showInNavigation());
    }

    public function testNavigationMenuPriority()
    {
        $this->assertSame(999, (new PublicationPage('', [], '', $this->pubType()))->navigationMenuPriority());
    }

    public function testNavigationMenuLabel()
    {
        $this->assertSame('Foo', (new PublicationPage('foo', [], '', $this->pubType()))->navigationMenuLabel());
    }

    public function testNavigationMenuGroup()
    {
        $this->assertNull((new PublicationPage('foo', [], '', $this->pubType()))->navigationMenuGroup());
    }

    public function testGetBladeView()
    {
        $this->assertSame('__dynamic', (new PublicationPage('foo', [], '', $this->pubType()))->getBladeView());
    }

    public function testFiles()
    {
        $this->assertSame([], PublicationPage::files());
    }

    public function testData()
    {
        $this->assertSame('directory/foo', (new PublicationPage('foo', [], '', $this->pubType()))->data('identifier'));
    }

    public function testGet()
    {
        $page = new PublicationPage('foo', [], '', $this->pubType());
        Hyde::pages()->put($page->getSourcePath(), $page);
        $this->assertEquals($page, PublicationPage::get('directory/foo'));
    }

    public function testParse()
    {
        // TODO
        $this->markTestSkipped('https://github.com/hydephp/develop/pull/685#discussion_r1033004814');
        $this->file(PublicationPage::sourcePath('directory/foo'));
        $this->assertInstanceOf(PublicationPage::class, PublicationPage::parse('directory/foo'));
        deleteDirectory(Hyde::path('directory'));
    }

    public function testGetRouteKey()
    {
        $this->assertSame('directory/foo', (new PublicationPage('foo', [], '', $this->pubType()))->getRouteKey());
    }

    public function testHtmlTitle()
    {
        $this->assertSame('HydePHP - Foo', (new PublicationPage('foo', [], '', $this->pubType()))->htmlTitle());
    }

    public function testAll()
    {
        $this->assertInstanceOf(PageCollection::class, PublicationPage::all());
    }

    public function testMetadata()
    {
        $this->assertInstanceOf(PageMetadataBag::class, (new PublicationPage('', [], '', $this->pubType()))->metadata());
    }

    public function test__construct()
    {
        $this->assertInstanceOf(PublicationPage::class, new PublicationPage('', [], '', $this->pubType()));
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf(Route::class, (new PublicationPage('', [], '', $this->pubType()))->getRoute());
    }

    public function testGetIdentifier()
    {
        $this->assertSame('directory/foo', (new PublicationPage('foo', [], '', $this->pubType()))->getIdentifier());
    }

    public function testHas()
    {
        $this->assertTrue((new PublicationPage('foo', [], '', $this->pubType()))->has('identifier'));
    }

    public function testToCoreDataObject()
    {
        $this->assertInstanceOf(CoreDataObject::class, (new PublicationPage('foo', [], '', $this->pubType()))->toCoreDataObject());
    }

    public function testConstructFactoryData()
    {
        (new PublicationPage('', [], '', $this->pubType()))->constructFactoryData($this->mockPageDataFactory());
        $this->assertTrue(true);
    }

    public function testCompile()
    {
        mkdir(Hyde::path('directory'));
        touch(Hyde::path('directory/detailTemplate.blade.php'));

        $page = new PublicationPage('foo', [], '', $this->pubType());
        Hyde::shareViewData($page);
        $this->assertIsString(PublicationPage::class, $page->compile());
        deleteDirectory(Hyde::path('directory'));
    }

    public function testMatter()
    {
        $this->assertInstanceOf(FrontMatter::class, (new PublicationPage('foo', [], '', $this->pubType()))->matter());
    }

    public function testMarkdown()
    {
        $this->assertInstanceOf(Markdown::class, (new PublicationPage('test-publication/foo', type: $this->pubType()))->markdown());
    }

    public function testSave()
    {
        mkdir(Hyde::path('directory'));
        $page = new PublicationPage('foo', type: $this->pubType());
        $this->assertSame($page, $page->save());
        $this->assertFileExists('directory/foo.md');
        unlink(Hyde::path('directory/foo.md'));
        rmdir(Hyde::path('directory'));
    }

    protected function pubType(): PublicationType
    {
        return new PublicationType(
            'name',
            'canonicalField',
            'sortField',
            'sortDirection',
            1,
            true,
            'detailTemplate',
            'listTemplate',
            [],
            'directory'
        );
    }
}
