<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Actions\CreatesNewPublicationType;
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

        $creator = new CreatesNewPublicationType('Test Publication', collect());
        $creator->create();
    }

    public function testCompilingWithPublicationTypeThatUsesThePublishedPaginatedViews()
    {
        $this->markTestIncomplete();
    }

    public function testCompilingWithPublicationTypeThatUsesTheVendorViews()
    {
        $this->markTestIncomplete();
    }

    public function testCompilingWithPublicationTypeThatUsesThePaginatedVendorViews()
    {
        $this->markTestIncomplete();
    }
}
