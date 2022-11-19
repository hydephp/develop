<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function deleteDirectory;
use function file_put_contents;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;
use function json_encode;
use function mkdir;
use function resource_path;

/**
 * @covers \Hyde\Pages\PublicationPage
 */
class PublicationPageTest extends TestCase
{
    public function test_publication_pages_are_routable()
    {
        mkdir(Hyde::path('test-publication'));
        $this->createPublicationFiles();

        $page = new PublicationPage(new PublicationType('test-publication/schema.json'), 'foo');

        $this->assertInstanceOf(Route::class, $page->getRoute());
        $this->assertEquals(new Route($page), $page->getRoute());
        $this->assertSame($page->getRoute()->getLink(), $page->getLink());
        $this->assertArrayHasKey($page->getSourcePath(), Hyde::pages());
        $this->assertArrayHasKey($page->getRouteKey(), Hyde::routes());

        deleteDirectory(Hyde::path('test-publication'));
    }

    public function test_publication_pages_are_discoverable()
    {
        mkdir(Hyde::path('test-publication'));
        $this->createPublicationFiles();

        $collection = Hyde::pages()->getPages();
        $this->assertInstanceOf(PublicationPage::class, $collection->get('__publications/foo.md'));

        deleteDirectory(Hyde::path('test-publication'));
    }

    public function test_publication_pages_are_properly_parsed()
    {
        mkdir(Hyde::path('test-publication'));
        $this->createPublicationFiles();

        $page = Hyde::pages()->getPages()->get('__publications/foo.md');
        $this->assertInstanceOf(PublicationPage::class, $page);
        $this->assertEquals('foo', $page->getIdentifier());
        $this->assertEquals('bar', $page->matter('foo'));
        $this->assertEquals('canonical', $page->matter('__canonical'));
        $this->assertEquals("Hello World!\n", $page->markdown()->body());

        deleteDirectory(Hyde::path('test-publication'));
    }

    public function test_publication_pages_are_compilable()
    {
        mkdir(Hyde::path('test-publication'));
        $this->createRealPublicationFiles();

        $page = Hyde::pages()->getPages()->get('__publications/foo.md');

        Hyde::shareViewData($page);
        $this->assertStringContainsString('Hello World!', $page->compile());

        deleteDirectory(Hyde::path('test-publication'));
    }

    protected function createRealPublicationFiles(): void
    {
        file_put_contents(Hyde::path('test-publication/schema.json'), '{
  "name": "test",
  "canonicalField": "slug",
  "sortField": "__createdAt",
  "sortDirection": "DESC",
  "pagesize": 0,
  "prevNextLinks": true,
  "detailTemplate": "test_detail.blade.php",
  "listTemplate": "test_list.blade.php",
  "fields": [
    {
      "name": "slug",
      "min": "4",
      "max": "32",
      "type": "string"
    }
  ]
}');
        file_put_contents(
            Hyde::path('test-publication/foo.md'),
            '---
__canonical: canonical
__createdAt: 2022-11-16 11:32:52
foo: bar
---

Hello World!
'
        );

        // Temporary until we settle on where to store templates
        @mkdir(resource_path('views/pubtypes'));
        $this->file('resources/views/pubtypes/test_list.blade.php');
        $this->file('resources/views/pubtypes/test_detail.blade.php', '{{ ($publication->markdown()->body()) }}');
    }

    protected function createPublicationFiles(): void
    {
        file_put_contents(Hyde::path('test-publication/schema.json'), json_encode(['foo' => 'bar']));
        file_put_contents(
            Hyde::path('test-publication/foo.md'),
            '---
__canonical: canonical
__createdAt: 2022-11-16 11:32:52
foo: bar
---

Hello World!
'
        );
    }
}
