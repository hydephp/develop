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

/**
 * @covers \Hyde\Pages\PublicationPage
 */
class PublicationPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        mkdir(Hyde::path('test-publication'));
    }

    protected function tearDown(): void
    {
        deleteDirectory(Hyde::path('test-publication'));

        parent::tearDown();
    }

    public function test_source_path_mappings()
    {
        $this->createPublicationFiles();

        $page = new PublicationPage(PublicationType::fromFile('test-publication/schema.json'), 'foo');

        $this->assertSame('test-publication/foo', $page->getIdentifier());
        $this->assertSame('test-publication/foo', $page->getRouteKey());
        $this->assertSame('test-publication/foo.md', $page->getSourcePath());
        $this->assertSame('test-publication/foo.html', $page->getOutputPath());
    }

    public function test_publication_pages_are_routable()
    {
        $this->createPublicationFiles();

        $page = new PublicationPage(PublicationType::fromFile('test-publication/schema.json'), 'foo');

        $this->assertInstanceOf(Route::class, $page->getRoute());
        $this->assertEquals(new Route($page), $page->getRoute());
        $this->assertSame($page->getRoute()->getLink(), $page->getLink());
        $this->assertArrayHasKey($page->getSourcePath(), Hyde::pages());
        $this->assertArrayHasKey($page->getRouteKey(), Hyde::routes());
    }

    public function test_publication_pages_are_discoverable()
    {
        $this->createPublicationFiles();

        $collection = Hyde::pages()->getPages();
        $this->assertInstanceOf(PublicationPage::class, $collection->get('test-publication/foo.md'));
    }

    public function test_publication_pages_are_properly_parsed()
    {
        $this->createPublicationFiles();

        $page = Hyde::pages()->getPages()->get('test-publication/foo.md');
        $this->assertInstanceOf(PublicationPage::class, $page);
        $this->assertEquals('test-publication/foo', $page->getIdentifier());
        $this->assertEquals('Foo', $page->title);

        $this->assertEquals('bar', $page->matter('foo'));
        $this->assertEquals('canonical', $page->matter('__canonical'));
        $this->assertEquals("Hello World!\n", $page->markdown()->body());
    }

    public function test_publication_pages_are_compilable()
    {
        $this->createRealPublicationFiles();

        $page = Hyde::pages()->getPages()->get('test-publication/foo.md');

        Hyde::shareViewData($page);
        $this->assertStringContainsString('Hello World!', $page->compile());
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
  "detailTemplate": "test_detail",
  "listTemplate": "test_list",
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
        file_put_contents(Hyde::path('test-publication/test_detail.blade.php'), '{{ ($publication->markdown()->body()) }}');
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
