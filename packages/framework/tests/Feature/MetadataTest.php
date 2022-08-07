<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Metadata\Metadata
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
