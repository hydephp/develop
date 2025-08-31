<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationListPage;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Publications\PublicationsExtension;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\PublicationsExtension::class)]
class PublicationsExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDefaultPages();
    }

    protected function tearDown(): void
    {
        $this->restoreDefaultPages();

        parent::tearDown();
    }

    public function testGetPageClassesMethod()
    {
        $this->assertSame([], PublicationsExtension::getPageClasses());
    }

    public function testGetTypesMethod()
    {
        $extension = new PublicationsExtension;
        $extension->discoverFiles(Hyde::files());
        $this->assertSame([], $extension->getTypes()->toArray());
    }

    public function testGetTypesMethodWithTypes()
    {
        $this->createPublication();

        $extension = new PublicationsExtension;
        $extension->discoverFiles(Hyde::files());
        $this->assertEquals(['publication' => PublicationType::get('publication')], $extension->getTypes()->all());
    }

    public function testGetTypesMethodWithMultipleTypes()
    {
        $this->createPublication();

        $this->directory('publication2');
        (new PublicationType('publication2'))->save();

        $extension = new PublicationsExtension;
        $extension->discoverFiles(Hyde::files());
        $this->assertEquals([
            'publication' => PublicationType::get('publication'),
            'publication2' => PublicationType::get('publication2'),
        ], $extension->getTypes()->all());
    }

    public function testPublicationFilesAreDiscovered()
    {
        $this->createPublication();

        $booted = FileCollection::init(Hyde::getInstance())->boot();

        $files = $booted->getFiles()->keys()->toArray();
        $this->assertSame(['publication/foo.md'], $files);

        $this->assertInstanceOf(SourceFile::class, $booted->getFiles()->get('publication/foo.md'));
        $this->assertEquals(new SourceFile('publication/foo.md', PublicationPage::class),
            $booted->getFiles()->get('publication/foo.md')
        );
    }

    public function testPublicationFilesAreDiscoveredForMultipleTypes()
    {
        $this->createPublication();

        $this->directory('publication2');
        (new PublicationType('publication2'))->save();
        $this->file('publication2/bar.md', 'bar');

        $booted = FileCollection::init(Hyde::getInstance())->boot();

        $files = $booted->getFiles()->keys()->toArray();
        $this->assertSame(['publication/foo.md', 'publication2/bar.md'], $files);
    }

    public function testPublicationMediaFilesAreDiscovered()
    {
        $this->directory('_media/publication');
        $this->file('_media/publication/foo.jpg', 'foo');

        $files = collect(MediaFile::all());
        $this->assertSame(['app.css', 'publication/foo.jpg'], MediaFile::files());
        $this->assertInstanceOf(MediaFile::class, $files->get('publication/foo.jpg'));
    }

    public function testBasePublicationPagesAreDiscovered()
    {
        $this->createPublication();

        $this->assertSame([
            'publication/foo.md',
            'publication/index',
        ], PageCollection::init(Hyde::getInstance())->boot()->getPages()->keys()->toArray());
    }

    public function testPublicationPagesAreDiscovered()
    {
        $this->createPublication();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            'publication/foo.md',
        ], $booted->getPages(PublicationPage::class)->keys()->toArray());

        $this->assertInstanceOf(PublicationPage::class, $booted->getPages()->get('publication/foo.md'));
        $this->assertEquals(new PublicationPage('foo', [], '', PublicationType::get('publication')),
            $booted->getPages()->get('publication/foo.md')
        );
    }

    public function testListingPagesForPublicationsAreGenerated()
    {
        $this->createPublication();
        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertSame([
            'publication/index',
        ], $booted->getPages(PublicationListPage::class)->keys()->toArray());

        $this->assertInstanceOf(PublicationListPage::class, $booted->getPages()->get('publication/index'));
        $this->assertEquals(new PublicationListPage(PublicationType::get('publication')),
            $booted->getPages()->get('publication/index')
        );
    }

    public function testPaginatedListingPagesForPublicationsAreGenerated()
    {
        $this->createPublication();
        (new PublicationType('publication', pageSize: 1))->save();
        (new PublicationPage('bar', [], '', PublicationType::get('publication')))->save();

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(PublicationListPage::class, $booted->getPage('publication/index'));
        $this->assertInstanceOf(InMemoryPage::class, $booted->getPage('publication/page-1'));
        $this->assertInstanceOf(InMemoryPage::class, $booted->getPage('publication/page-2'));
    }

    public function testPublicationTagListPagesAreGenerated()
    {
        $this->directory('publication');

        (new PublicationType('publication', fields: [
            ['name' => 'tags', 'type' => 'tag'],
        ]))->save();

        $this->markdown('publication/foo.md', matter: ['tags' => ['foo', 'bar']]);

        $booted = PageCollection::init(Hyde::getInstance())->boot();

        $this->assertInstanceOf(InMemoryPage::class, $booted->getPage('tags/index'));
    }

    public function testPublicationTagListRoutesWithTagsAreGenerated()
    {
        $this->createPublication();
        (new PublicationType('publication', fields: [
            ['name' => 'tags', 'type' => 'tag'],
        ]))->save();

        $this->markdown('publication/foo.md', matter: ['tags' => ['foo', 'bar']]);

        $booted = PageCollection::init(Hyde::getInstance())->boot();
        $routes = $booted->getPages()->keys()->toArray();

        $this->assertSame([
            'publication/foo.md',
            'publication/index',
            'tags/index',
            'tags/foo',
            'tags/bar',
        ], $routes);

        // test tags
        $tagPage = $booted->getPages()->get('tags/index');
        $this->assertInstanceOf(InMemoryPage::class, $tagPage);
        $this->assertSame(['foo' => 1, 'bar' => 1], $tagPage->matter('tags'));
    }

    public function testPublicationRoutesAreDiscovered()
    {
        $this->createPublication();

        $booted = RouteCollection::init(Hyde::getInstance())->boot();
        $routes = $booted->getRoutes()->keys()->toArray();

        $this->assertSame([
            'publication/foo',
            'publication/index',
        ], $routes);

        $this->assertContainsOnlyInstancesOf(Route::class, $booted->getRoutes());

        $this->assertEquals(new Route(new PublicationPage('foo', [], '', PublicationType::get('publication'))),
            $booted->getRoutes()->get('publication/foo')
        );

        $this->assertEquals(new Route(new PublicationListPage(PublicationType::get('publication'))),
            $booted->getRoutes()->get('publication/index')
        );
    }

    public function testPublicationTagListRoutesAreDiscovered()
    {
        $this->directory('publication');

        (new PublicationType('publication', fields: [
            ['name' => 'tag', 'type' => 'tag'],
        ]))->save();

        $this->markdown('publication/foo.md', matter: ['tag' => ['foo']]);

        $booted = RouteCollection::init(Hyde::getInstance())->boot();
        $routes = $booted->getRoutes()->keys()->toArray();

        $this->assertSame([
            'publication/foo',
            'publication/index',
            'tags/index',
            'tags/foo',
        ], $routes);

        // test tags
        $tagPage = $booted->getRoutes()->get('tags/index')->getPage();
        $this->assertInstanceOf(InMemoryPage::class, $tagPage);
        $this->assertSame(['foo' => 1], $tagPage->matter('tags'));
    }

    public function testPublicationTagListRoutesWithTagsAreDiscovered()
    {
        $this->createPublication();
        (new PublicationType('publication', fields: [
            ['name' => 'tags', 'type' => 'tag'],
        ]))->save();

        $this->markdown('publication/foo.md', matter: ['tags' => ['foo', 'bar']]);

        $booted = RouteCollection::init(Hyde::getInstance())->boot();
        $routes = $booted->getRoutes()->keys()->toArray();

        $this->assertSame([
            'publication/foo',
            'publication/index',
            'tags/index',
            'tags/foo',
            'tags/bar',
        ], $routes);

        // test tags
        $tagPage = $booted->getRoutes()->get('tags/index')->getPage();
        $this->assertInstanceOf(InMemoryPage::class, $tagPage);
        $this->assertSame(['foo' => 1, 'bar' => 1], $tagPage->matter('tags'));
    }

    protected function createPublication(): void
    {
        $this->directory('publication');

        (new PublicationType('publication'))->save();
        (new PublicationPage('foo', [], '', PublicationType::get('publication')))->save();
    }
}
