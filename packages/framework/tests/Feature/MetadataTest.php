<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Metadata\Metadata
 * @covers \Hyde\Framework\Models\Metadata\LinkItem
 * @covers \Hyde\Framework\Models\Metadata\MetadataItem
 * @covers \Hyde\Framework\Models\Metadata\OpenGraphItem
 */
class MetadataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => null]);
        config(['hyde.meta' => []]);
    }
}
