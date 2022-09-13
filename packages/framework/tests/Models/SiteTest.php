<?php

namespace Hyde\Framework\Testing\Models;

use Hyde\Framework\Models\Site;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Site
 */
class SiteTest extends TestCase
{
    public function testName()
    {
        $this->assertSame('HydePHP', Site::name());

        config(['site.name' => 'foo']);
        $this->assertSame('foo', Site::name());
    }

    public function testUrl()
    {
        config(['site.url' => null]);
        $this->assertNull(Site::url());

        config(['site.url' => 'https://example.com']);
        $this->assertSame('https://example.com', Site::url());
    }

    public function testLanguage()
    {
        config(['site.language' => null]);
        $this->assertNull(Site::language());

        config(['site.language' => 'en']);
        $this->assertSame('en', Site::language());
    }
}
