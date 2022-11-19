<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Framework\Features\Publications\Models;

use function file_put_contents;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use function json_encode;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationListPage
 */
class PublicationListPageTest extends TestCase
{
    public function test_source_path_mappings()
    {
        $this->createPublicationFiles();

        $page = new PublicationListPage($this->getPublicationType());
        $this->assertSame('test-publication/index', $page->getIdentifier());
        $this->assertSame('test-publication/index', $page->getRouteKey());
        $this->assertSame('test-publication/index.html', $page->getOutputPath());
        $this->assertSame('__publications/test-publication/index.json', $page->getSourcePath());

        File::deleteDirectory(Hyde::path('test-publication'));
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
