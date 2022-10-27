<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Metadata\GlobalMetadataBag;
use Hyde\Support\Models\Site;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\Models\Site
 */
class SiteTest extends TestCase
{
    public function testConstruct()
    {
        $site = new Site();

        $this->assertNotNull($site->name);
        $this->assertNotNull($site->language);
        $this->assertNotNull($site->url);

        $this->assertEquals(config('site.name'), $site->name);
        $this->assertEquals(config('site.language'), $site->language);
        $this->assertEquals(config('site.url'), $site->url);

        $this->assertSame(Site::name(), $site->name);
        $this->assertSame(Site::language(), $site->language);
        $this->assertSame(Site::url(), $site->url);
    }

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
