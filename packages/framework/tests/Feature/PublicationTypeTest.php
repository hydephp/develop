<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationType
 */
class PublicationTypeTest extends TestCase
{
    public function testCanConstructNewPublicationType()
    {
        $publicationType = new PublicationType('test', 'canonical', 'sort', 'asc', 10, true, 'detail', 'list', ['foo' => 'bar']);

        $this->assertSame('test', $publicationType->name);
        $this->assertSame('canonical', $publicationType->canonicalField);
        $this->assertSame('sort', $publicationType->sortField);
        $this->assertSame('asc', $publicationType->sortDirection);
        $this->assertSame(10, $publicationType->pagesize);
        $this->assertSame(true, $publicationType->prevNextLinks);
        $this->assertSame('detail', $publicationType->detailTemplate);
        $this->assertSame('list', $publicationType->listTemplate);
        $this->assertSame(['foo' => 'bar'], $publicationType->fields);
    }
}
