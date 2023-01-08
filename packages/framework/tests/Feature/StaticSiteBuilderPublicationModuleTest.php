<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\TestCase;

/**
 * Tests that publication pages are compiled properly when building the static site.
 */
class StaticSiteBuilderPublicationModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.throw_on_console_exception' => true]);
    }

    public function testCompilingWithPublicationTypeThatUsesThePublishedViews()
    {
        $this->directory('test-publication');

        $this->artisan('make:publicationType "Test Publication" --use-defaults')
            ->assertSuccessful();
    }

    public function testCompilingWithPublicationTypeThatUsesThePublishedPaginatedViews()
    {

    }

    public function testCompilingWithPublicationTypeThatUsesTheVendorViews()
    {

    }

    public function testCompilingWithPublicationTypeThatUsesThePaginatedVendorViews()
    {
        
    }
}
