<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Facades\Filesystem;
use Hyde\Foundation\Facades\Files;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Publications\Actions\GeneratesPublicationTagPages;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Support\Filesystem\SourceFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function glob;
use function range;
use function str_ends_with;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationsExtensionTest
 */
class PublicationsExtension extends HydeExtension
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationType> */
    protected Collection $types;

    /** @return \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationType> */
    public function getTypes(): Collection
    {
        $this->constructTypesIfNotConstructed();

        return $this->types;
    }

    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public static function getPageClasses(): array
    {
        return [
            // Since our page classes are not auto-discoverable by Hyde due to the dynamic source directories,
            // we run our own discovery logic in the callbacks below.
        ];
    }

    public function discoverFiles(FileCollection $collection): void
    {
        $this->types = new Collection(); // Reset (only called if we are in a test environment)
        $this->types = $this->parsePublicationTypes();

        $this->types->each(function (PublicationType $type) use ($collection): void {
            Collection::make($this->getPublicationFilesForType($type))->map(function (string $filepath) use ($collection): void {
                $collection->put(Hyde::pathToRelative($filepath), SourceFile::make($filepath, PublicationPage::class));
            });
        });
    }

    public function discoverPages(PageCollection $collection): void
    {
        $this->discoverPublicationPages($collection);

        if (Filesystem::exists('tags.yml')) {
            $this->generatePublicationTagPages($collection);
        }
    }

    protected function discoverPublicationPages(PageCollection $instance): void
    {
        $this->types->each(function (PublicationType $type) use ($instance): void {
            $this->discoverPublicationPagesForType($type, $instance);
            $this->generatePublicationListingPageForType($type, $instance);
        });
    }

    protected function discoverPublicationPagesForType(PublicationType $type, PageCollection $instance): void
    {
        // TODO this can be simplified as we probably don't need to add the pages on a per-type basis

        $collection = Files::getSourceFiles(PublicationPage::class)->filter(function (SourceFile $file) use ($type): bool {
            return str_starts_with($file->getPath(), $type->getDirectory());
        });
        $collection->each(function (SourceFile $file) use ($instance): void {
            $instance->addPage(PublicationPage::parse(Str::before($file->getPath(), PublicationPage::fileExtension())));
        });
    }

    protected function generatePublicationListingPageForType(PublicationType $type, PageCollection $instance): void
    {
        $page = new PublicationListPage($type);
        $instance->put($page->getSourcePath(), $page);

        if ($type->usesPagination()) {
            $this->generatePublicationPaginatedListingPagesForType($type, $instance);
        }
    }

    protected function generatePublicationPaginatedListingPagesForType(PublicationType $type, PageCollection $instance): void
    {
        $paginator = $type->getPaginator();

        foreach (range(1, $paginator->totalPages()) as $page) {
            $paginator->setCurrentPage($page);
            $listTemplate = $type->listTemplate;
            if (str_ends_with($listTemplate, '.blade.php')) {
                $listTemplate = "{$type->getDirectory()}/$listTemplate";
            }
            $listingPage = new InMemoryPage("{$type->getDirectory()}/page-$page", [
                'publicationType' => $type, 'paginatorPage' => $page,
                'title' => $type->name.' - Page '.$page,
            ], view: $listTemplate);
            $instance->put($listingPage->getSourcePath(), $listingPage);
        }
    }

    protected function generatePublicationTagPages(PageCollection $collection): void
    {
        (new GeneratesPublicationTagPages($collection))->__invoke();
    }

    /** @return Collection<string, PublicationPage> */
    protected function parsePublicationTypes(): Collection
    {
        return Collection::make($this->getSchemaFiles())->mapWithKeys(function (string $schemaFile): array {
            $type = PublicationType::fromFile(Hyde::pathToRelative($schemaFile));

            return [$type->getDirectory() => $type];
        });
    }

    protected function getSchemaFiles(): array
    {
        return glob(Hyde::path(Hyde::getSourceRoot()).'/*/schema.json');
    }

    protected function getPublicationFiles(string $directory): array
    {
        return glob(Hyde::path("$directory/*.md"));
    }

    protected function getPublicationFilesForType(PublicationType $type): array
    {
        return $this->getPublicationFiles($type->getDirectory());
    }

    /** @experimental This feature may be removed pending actual necessity,
     *               as the array would only be uninitialized when the kernel has not yet booted,
     *               a point which may actually be too early to actually interact with this domain.
     *               Nonetheless, it's present for compatability during the ongoing container refactor.
     *
     * @codeCoverageIgnore
     */
    private function constructTypesIfNotConstructed(): void
    {
        if (! isset($this->types)) {
            //
        }
    }
}
