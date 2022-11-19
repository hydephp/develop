<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Hyde\Support\Models\Route;
use Hyde\Testing\TestCase;

use function deleteDirectory;
use function file_put_contents;
use function json_encode;
use function mkdir;

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

    public function test_publication_pages_are_compilable()
    {
        mkdir(Hyde::path('test-publication'));
        $this->createPublicationFiles();

        $page = new PublicationPage(new PublicationType('test-publication/schema.json'), 'foo');

        $this->assertStringContainsString('Hello World!', $page->compile());

        deleteDirectory(Hyde::path('test-publication'));
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
