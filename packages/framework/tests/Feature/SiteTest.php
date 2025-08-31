<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Site;
use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Facades\Site::class)]
class SiteTest extends TestCase
{
    public function testUrl()
    {
        $this->assertSame('http://localhost', Site::url());

        $this->withoutSiteUrl();
        $this->assertNull(Site::url());

        $this->withSiteUrl();
        $this->assertSame('https://example.com', Site::url());
    }

    public function testName()
    {
        $this->assertSame('HydePHP', Site::name());

        config(['hyde.name' => null]);
        $this->assertNull(Site::name());

        config(['hyde.name' => 'foo']);
        $this->assertSame('foo', Site::name());
    }

    public function testLanguage()
    {
        $this->assertSame('en', Site::language());

        config(['hyde.language' => null]);
        $this->assertNull(Site::language());

        config(['hyde.language' => 'foo']);
        $this->assertSame('foo', Site::language());
    }

    public function testMetadata()
    {
        $this->assertEquals(GlobalMetadataBag::make(), Site::metadata());
    }
}
