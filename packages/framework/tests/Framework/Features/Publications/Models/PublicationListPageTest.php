<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Framework\Features\Publications\Models;

use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Hyde\Testing\TestCase;

use Illuminate\Support\Facades\File;

use function file_put_contents;
use function json_encode;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationListPage
 */
class PublicationListPageTest extends TestCase
{
    public function testSourcePathMappings()
    {
        $this->createPublicationFiles();

        $page = new PublicationListPage($this->getPublicationType());
        $this->assertSame('test-publication/index', $page->getIdentifier());
        $this->assertSame('test-publication/index', $page->getRouteKey());
        $this->assertSame('test-publication/index.html', $page->getOutputPath());


        File::deleteDirectory(Hyde::path('publications'));
    }

    protected function createPublicationFiles(): void
    {
        mkdir(Hyde::path('test-publication'));
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

    protected function getPublicationType(): PublicationType
    {
        return new PublicationType('test-publication/schema.json');
    }
}
