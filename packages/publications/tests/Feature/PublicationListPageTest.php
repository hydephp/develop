<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationListPage;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Pages\PublicationListPage::class)]
class PublicationListPageTest extends TestCase
{
    public function testSourcePathMappings()
    {
        $this->createPublicationFiles();

        $page = new PublicationListPage($this->getPublicationType());
        $this->assertSame('test-publication/index', $page->getIdentifier());
        $this->assertSame('test-publication/index', $page->getRouteKey());
        $this->assertSame('test-publication/index.html', $page->getOutputPath());
        $this->assertSame('test-publication/index', $page->getSourcePath());

        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function testListingPageCanBeCompiled()
    {
        $this->createPublicationFiles();

        file_put_contents(Hyde::path('test-publication/list.blade.php'), 'Listing Page');

        $page = new PublicationListPage($this->getPublicationType());

        Hyde::shareViewData($page);
        $this->assertStringContainsString('Listing Page', $page->compile());
        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function testListPageCanShowUpInNavigation()
    {
        $this->createPublicationFiles();

        $page = new PublicationListPage($this->getPublicationType());

        $this->assertTrue($page->showInNavigation());

        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function testListPageIsNotAddedToNavigationWhenPublicationIdentifierIsSetInConfig()
    {
        config(['hyde.navigation.exclude' => ['test-publication']]);

        $this->createPublicationFiles();

        $page = new PublicationListPage($this->getPublicationType());

        $this->assertFalse($page->showInNavigation());

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
            'name' => 'test',
            'canonicalField' => 'canonical',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'sortField' => 'sort',
            'sortAscending' => true,
            'pageSize' => 10,
            'fields' => [
                [
                    'type' => 'string',
                    'name' => 'Foo',
                ],
            ],
        ];
    }
}
