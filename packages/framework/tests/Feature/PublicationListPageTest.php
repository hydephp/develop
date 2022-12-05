<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

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
        $this->assertSame('test-publication/schema.json', $page->getSourcePath());

        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function test_listing_page_can_be_compiled()
    {
        $this->createPublicationFiles();

        file_put_contents(Hyde::path('test-publication/list.blade.php'), 'Listing Page');

        $page = new PublicationListPage($this->getPublicationType());

        Hyde::shareViewData($page);
        $this->assertStringContainsString('Listing Page', $page->compile());
        File::deleteDirectory(Hyde::path('test-publication'));
    }

    protected function createPublicationFiles(): void
    {
        mkdir(Hyde::path('test-publication'));
        file_put_contents(Hyde::path('test-publication/schema.json'), json_encode($this->getTestData()));
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
        return PublicationType::fromFile('test-publication/schema.json');
    }

    protected function getTestData(): array
    {
        return [
            'name'           => 'test',
            'canonicalField' => 'canonical',
            'detailTemplate' => 'detail',
            'listTemplate'   => 'list',
            'pagination' => [
                'sortField'      => 'sort',
                'sortAscending'  => 'asc',
                'pageSize'       => 10,
                'prevNextLinks'  => true,
            ],
            'fields'         => [
                'foo' => 'bar',
            ],
        ];
    }
}
