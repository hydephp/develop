<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * Tests that publication pages are compiled properly when building the static site.
 *
 * These tests provide a high level overview of the entire publications feature.
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
        // Setup

        $this->directory('test-publication');

        $creator = new CreatesNewPublicationType('Test Publication', $this->getAllFields());
        $creator->create();

        $this->assertCount(3, Filesystem::files('test-publication'));

        $this->assertFileExists('test-publication/schema.json');
        $this->assertFileExists('test-publication/detail.blade.php');
        $this->assertFileExists('test-publication/list.blade.php');

        // Test site build without any publications

        $this->artisan('build')->assertSuccessful();

        $this->assertCount(1, Filesystem::files('_site/test-publication'));
        $this->assertFileExists('_site/test-publication/index.html');

        $this->resetSite();
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

    protected function getAllFields(): Collection
    {
        $types = PublicationFieldTypes::collect();

        $array = [];
        foreach ($types as $type) {
            $array[] = [
                'name' => "{$type->name}Field",
                'type' => $type->value,
            ];
        }

        return collect($array);
    }
}
