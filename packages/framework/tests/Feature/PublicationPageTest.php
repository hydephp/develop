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
        mkdir(Hyde::path('publication'));
        file_put_contents(Hyde::path('publication/schema.json'), json_encode(['foo' => 'bar']));
        file_put_contents(Hyde::path('publication/foo.md'), '---
__canonical: canonical
__createdAt: 2022-11-16 11:32:52
foo: bar
---

Hello World!
');

        $page = new PublicationPage(new PublicationType('publication/schema.json'), 'foo');

        $this->assertInstanceOf(Route::class, $page->getRoute());
        $this->assertEquals(new Route($page), $page->getRoute());
        $this->assertSame($page->getRoute()->getLink(), $page->getLink());
        $this->assertArrayHasKey($page->getSourcePath(), Hyde::pages());
        $this->assertArrayHasKey($page->getRouteKey(), Hyde::routes());

        deleteDirectory(Hyde::path('publication'));
    }
}
