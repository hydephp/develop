<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Pages\PublicationPage::class)]
class PublicationPageTest extends TestCase
{
    public function testSourcePathMappings()
    {
        $this->createPublicationFiles();

        $page = new PublicationPage('foo', [], '', PublicationType::fromFile('test-publication/schema.json'));

        $this->assertSame('test-publication/foo', $page->getIdentifier());
        $this->assertSame('test-publication/foo', $page->getRouteKey());
        $this->assertSame('test-publication/foo.md', $page->getSourcePath());
        $this->assertSame('test-publication/foo.html', $page->getOutputPath());
    }

    public function testPublicationPagesAreRoutable()
    {
        $this->createPublicationFiles();

        $page = PublicationPage::get('test-publication/foo');
        $this->assertInstanceOf(Route::class, $page->getRoute());
        $this->assertEquals(new Route($page), $page->getRoute());
        $this->assertSame($page->getRoute()->getLink(), $page->getLink());
        $this->assertArrayHasKey($page->getSourcePath(), Hyde::pages());
        $this->assertArrayHasKey($page->getRouteKey(), Hyde::routes());
    }

    public function testPublicationPagesAreDiscoverable()
    {
        $this->createPublicationFiles();

        $collection = Hyde::pages()->getPages();
        $this->assertInstanceOf(PublicationPage::class, $collection->get('test-publication/foo.md'));
    }

    public function testPublicationPagesAreProperlyParsed()
    {
        $this->createPublicationFiles();

        $page = Hyde::pages()->getPages()->get('test-publication/foo.md');
        $this->assertInstanceOf(PublicationPage::class, $page);
        $this->assertSame('test-publication/foo', $page->getIdentifier());
        $this->assertSame('Foo', $page->title);

        $this->assertSame('bar', $page->matter('foo'));
        $this->assertSame('canonical', $page->matter('__canonical'));
        $this->assertSame('Hello World!', $page->markdown()->body());
    }

    public function testPublicationPagesAreParsable()
    {
        $this->directory('test-publication');

        (new PublicationType('test-publication'))->save();

        $this->file('test-publication/foo.md', <<<'MD'
            ---
            __createdAt: 2022-11-27 21:07:37
            title: My Title
            ---

            ## Write something awesome.


            MD
        );

        $page = PublicationPage::parse('test-publication/foo');
        $this->assertInstanceOf(PublicationPage::class, $page);
        $this->assertSame('test-publication/foo', $page->identifier);
        $this->assertSame('## Write something awesome.', $page->markdown()->body());
        $this->assertSame('My Title', $page->title);
        $this->assertSame('My Title', $page->matter->get('title'));
        $this->assertTrue($page->matter->has('__createdAt'));
    }

    public function testPublicationPagesAreCompilable()
    {
        $this->createRealPublicationFiles();

        $page = Hyde::pages()->getPages()->get('test-publication/foo.md');

        Hyde::shareViewData($page);
        $this->assertStringContainsString('Hello World!', $page->compile());
    }

    public function testIdentifierPassedConstructorIsNormalized()
    {
        $this->createPublicationFiles();
        $type = PublicationType::fromFile('test-publication/schema.json');

        $page1 = new PublicationPage('foo', [], '', $type);
        $page2 = new PublicationPage('test-publication/foo', [], '', $type);

        $this->assertSame('test-publication/foo', $page1->getIdentifier());
        $this->assertSame('test-publication/foo', $page2->getIdentifier());

        $this->assertEquals($page1, $page2);
    }

    public function testIdentifierNormalizerDoesNotAffectDirectoryWithSameNameAsIdentifier()
    {
        $this->createPublicationFiles();
        $type = PublicationType::fromFile('test-publication/schema.json');

        $page = new PublicationPage('test-publication/test-publication', [], '', $type);
        $this->assertSame('test-publication/test-publication', $page->getIdentifier());
    }

    protected function createRealPublicationFiles(): void
    {
        $this->directory('test-publication');
        file_put_contents(Hyde::path('test-publication/schema.json'), '{
  "name": "test",
  "canonicalField": "slug",
  "detailTemplate": "test_detail.blade.php",
  "listTemplate": "test_list.blade.php",
  "sortField": "__createdAt",
  "sortAscending": true,
  "pageSize": 0,
  "fields": [
    {
      "name": "slug",
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

        file_put_contents(Hyde::path('test-publication/test_detail.blade.php'), '{{ ($publication->markdown()->body()) }}');
    }

    protected function createPublicationFiles(): void
    {
        $this->directory('test-publication');
        (new PublicationType('Test Publication'))->save();

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
