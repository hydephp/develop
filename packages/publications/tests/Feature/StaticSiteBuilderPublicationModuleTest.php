<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use function collect;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Publications\Actions\CreatesNewPublicationType;
use Hyde\Publications\Actions\SeedsPublicationFiles;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use function range;

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
        $this->directory('test-publication');

        (new CreatesNewPublicationType('Test Publication', collect([])))->create();
        $this->assertCount(3, Filesystem::files('test-publication'));

        foreach (range(1, 5) as $i) {
            $this->file("test-publication/publication-$i.md", "## Test publication $i");
        }

        $this->artisan('build')->assertSuccessful();

        $this->assertSame([
            'index.html',
            'publication-1.html',
            'publication-2.html',
            'publication-3.html',
            'publication-4.html',
            'publication-5.html',
        ], $this->getFilenamesInDirectory('_site/test-publication'));

        $this->resetSite();
    }

    public function testCompilingWithPublicationTypeThatUsesTheVendorViews()
    {
        $this->directory('test-publication');

        (new CreatesNewPublicationType('Test Publication', collect([])))->create();
        $type = PublicationType::get('test-publication');
        $type->detailTemplate = 'hyde-publications::publication_detail';
        $type->listTemplate = 'hyde-publications::publication_list';
        $type->save();

        foreach (range(1, 5) as $i) {
            $this->file("test-publication/publication-$i.md", "## Test publication $i");
        }

        $this->artisan('build')->assertSuccessful();

        $this->assertSame([
            'index.html',
            'publication-1.html',
            'publication-2.html',
            'publication-3.html',
            'publication-4.html',
            'publication-5.html',
        ], $this->getFilenamesInDirectory('_site/test-publication'));

        $this->resetSite();
    }

    public function testCompilingWithPublicationTypeThatUsesThePublishedPaginatedViews()
    {
        $this->directory('test-publication');

        (new CreatesNewPublicationType('Test Publication', collect([])))->create();

        $type = PublicationType::get('test-publication');
        $type->listTemplate = 'hyde-publications::publication_paginated_list';
        $type->pageSize = 2;
        $type->save();

        foreach (range(1, 5) as $i) {
            $this->file("test-publication/publication-$i.md", "## Test publication $i");
        }

        $this->artisan('build')->assertSuccessful();

        $this->assertSame([
            'index.html',
            'page-1.html',
            'page-2.html',
            'page-3.html',
            'publication-1.html',
            'publication-2.html',
            'publication-3.html',
            'publication-4.html',
            'publication-5.html',
        ], $this->getFilenamesInDirectory('_site/test-publication'));

        // TODO test that the pagination links are correct

        $this->resetSite();
    }

    public function testCompilingWithPublicationTypeThatUsesThePaginatedVendorViews()
    {
        $this->directory('test-publication');

        (new CreatesNewPublicationType('Test Publication', collect([])))->create();
        // TODO assert the paginated template was published once we implement that

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

    protected function getFilenamesInDirectory(string $directory): array
    {
        return collect(Filesystem::files($directory))->map(fn ($file) => $file->getFilename())->toArray();
    }
}
