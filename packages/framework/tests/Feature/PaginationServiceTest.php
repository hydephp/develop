<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function collect;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PaginationService;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\PaginationService
 */
class PaginationServiceTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $publicationType = $this->setupPublication();

        $this->assertInstanceOf(PaginationService::class,
            new PaginationService($publicationType)
        );
    }

    public function testGetPaginatedPageCollection()
    {
        $publicationType = $this->setupPublication();

        $this->assertEquals(collect([]), (new PaginationService($publicationType))->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $publicationType = $this->setupPublication();

        foreach (range(1, 50) as $i) {
            $this->file("test-publication/$i.md", "title: $i");
        }

        $collection = (new PaginationService($publicationType))->getPaginatedPageCollection();
        $this->assertCount(2, $collection);
        $this->assertCount(25, $collection->first());
        $this->assertCount(25, $collection->last());
    }

    protected function setupPublication(): PublicationType
    {
        $this->directory('test-publication');
        $this->setupTestPublication();
        return PublicationType::get('test-publication');
    }
}
