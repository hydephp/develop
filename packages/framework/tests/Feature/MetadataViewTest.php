<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Metadata\Metadata
 */
class MetadataViewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => 'http://localhost']);
    }
}
