<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Actions\CreatesNewPublicationType;
use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Hyde;
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

    public function testCompilingWithPublicationTypeWithSeededFilesContainingAllFieldTypes()
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

        // Test site build with publications

        $seeder = new SeedsPublicationFiles(PublicationType::get('test-publication'), 5);
        $seeder->create();

        $this->assertCount(3 + 5, Filesystem::files('test-publication'));

        Hyde::boot(); // Reboot the kernel to discover the new publications

        $this->artisan('build')->assertSuccessful();

        $this->assertCount(1 + 5, Filesystem::files('_site/test-publication'));

        $this->resetSite();
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

        // Test site build with publications

        $seeder = new SeedsPublicationFiles(PublicationType::get('test-publication'), 5);
        $seeder->create();

        $this->assertCount(3 + 5, Filesystem::files('test-publication'));

        Hyde::boot(); // Reboot the kernel to discover the new publications

        $this->artisan('build')->assertSuccessful();

        $this->assertCount(1 + 5, Filesystem::files('_site/test-publication'));

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
        foreach ($types as $index => $type) {
            $array[$index] = [
                'name' => "{$type->name}Field",
                'type' => $type->value,
            ];

            if ($type === PublicationFieldTypes::Tag) {
                $array[$index]['tagGroup'] = 'myTagGroup';
            }
        }

        return collect($array);
    }
}
