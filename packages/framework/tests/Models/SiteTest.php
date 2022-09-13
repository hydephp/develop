<?php

namespace Hyde\Framework\Testing\Models;

use Hyde\Framework\Models\Site;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Site
 */
class SiteTest extends TestCase
{
    public function testGetBaseUrl()
    {
        config(['site.url' => null]);
        $this->assertNull(Site::getBaseUrl());

        config(['site.url' => 'https://example.com']);
        $this->assertSame('https://example.com', Site::getBaseUrl());
    }
}
