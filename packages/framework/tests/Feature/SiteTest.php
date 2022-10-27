<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Site;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Site
 */
class SiteTest extends TestCase
{

    public function testUrl()
    {
        $this->assertSame('http://localhost', Site::url());

        config(['site.url' => null]);
        $this->assertNull(Site::url());

        config(['site.url' => 'https://example.com']);
        $this->assertSame('https://example.com', Site::url());
    }

    public function testName()
    {
        $this->assertSame('HydePHP', Site::name());

        config(['site.name' => null]);
        $this->assertNull(Site::name());

        config(['site.name' => 'foo']);
        $this->assertSame('foo', Site::name());
    }

    public function testLanguage()
    {
        $this->assertSame('en', Site::language());

        config(['site.language' => null]);
        $this->assertNull(Site::language());

        config(['site.language' => 'foo']);
        $this->assertSame('foo', Site::language());
    }

    public function testMetadata()
    {
        $this->assertEquals(GlobalMetadataBag::make(), Site::metadata());
    }
}
