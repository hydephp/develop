<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Facades\Filesystem;
use Illuminate\Support\Collection;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Actions\SeedsPublicationFiles;
use Hyde\Publications\Concerns\PublicationFieldTypes;
use Hyde\Publications\Actions\CreatesNewPublicationType;

use function range;
use function collect;

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

        $this->throwOnConsoleException();
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
        $type->detailTemplate = 'hyde-publications::detail';
        $type->listTemplate = 'hyde-publications::list';
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

        (new CreatesNewPublicationType('Test Publication', collect([]), pageSize: 10))->create();

        $type = PublicationType::get('test-publication');
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

        $this->assertThePaginationLinksAreCorrect();

        $this->resetSite();
    }

    public function testCompilingWithPublicationTypeThatUsesThePaginatedVendorViews()
    {
        $this->directory('test-publication');

        (new CreatesNewPublicationType('Test Publication', collect([])))->create();

        $type = PublicationType::get('test-publication');
        $type->listTemplate = 'hyde-publications::paginated_list';
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

        $this->assertThePaginationLinksAreCorrect();

        $this->resetSite();
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
        }

        return collect($array);
    }

    protected function getFilenamesInDirectory(string $directory): array
    {
        return collect(Filesystem::files($directory))->map(fn ($file) => $file->getFilename())->toArray();
    }

    /** @noinspection HtmlUnknownTarget */
    protected function assertThePaginationLinksAreCorrect(): void
    {
        $this->assertHtmlHas(<<<'HTML'
            <div class="px-2">
            <strong>1</strong>
            <a href="../test-publication/page-2.html">2</a>                                                <a href="../test-publication/page-3.html">3</a>                        </div>

            <a href="../test-publication/page-2.html">&#8250;</a>    </nav>        </div>
            HTML, Filesystem::get('_site/test-publication/page-1.html')
        );

        $this->assertHtmlHas(<<<'HTML'
            <div class="px-2">
            <a href="../test-publication/page-1.html">1</a>                                                <strong>2</strong>
            <a href="../test-publication/page-3.html">3</a>                        </div>
            HTML, Filesystem::get('_site/test-publication/page-2.html')
        );

        $this->assertHtmlHas(<<<'HTML'
            <div class="px-2">
            <a href="../test-publication/page-1.html">1</a>                                                <a href="../test-publication/page-2.html">2</a>                                                <strong>3</strong>
            </div>
            HTML, Filesystem::get('_site/test-publication/page-3.html')
        );
    }

    protected function assertHtmlHas(string $expected, string $html): void
    {
        $this->assertStringContainsString($this->stripIndentationForEachLine($expected), $this->stripIndentationForEachLine($html));
    }

    protected function stripIndentationForEachLine(string $string): string
    {
        $array = explode("\n", $string);
        foreach ($array as $index => $line) {
            $array[$index] = ltrim($line);
        }

        return implode("\n", $array);
    }
}
