<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\PageCollection;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\PageCollection
 */
class PageCollectionTest extends TestCase
{
    public function test_boot_method_creates_new_page_collection_and_discovers_pages_automatically()
    {
        $collection = PageCollection::boot();
        $this->assertInstanceOf(PageCollection::class, $collection);
        $this->assertInstanceOf(Collection::class, $collection);

        $this->assertEquals([
            '_pages/404.blade.php' => new BladePage('404'),
            '_pages/index.blade.php' => new BladePage('index'),
        ], $collection->toArray());
    }
}
