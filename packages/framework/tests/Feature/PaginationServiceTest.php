<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use function collect;
use Hyde\Framework\Features\Publications\PaginationService;
use Hyde\Testing\TestCase;
use function range;

/**
 * @covers \Hyde\Framework\Features\Publications\PaginationService
 */
class PaginationServiceTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $paginationSettings = new PaginationSettings();

        $this->assertInstanceOf(PaginationService::class,
            new PaginationService($paginationSettings)
        );
    }

    public function testGetPaginatedPageCollection()
    {
        $paginationSettings = new PaginationSettings();

        $this->assertEquals(collect([]), (new PaginationService($paginationSettings))->getPaginatedPageCollection());
    }

    public function testGetPaginatedPageCollectionWithPages()
    {
        $paginationSettings = new PaginationSettings();

        $collection = (new PaginationService($paginationSettings, collect(range(1, 50))))->getPaginatedPageCollection();
        $this->assertCount(2, $collection);
        $this->assertCount(25, $collection->first());
        $this->assertCount(25, $collection->last());
    }
}
