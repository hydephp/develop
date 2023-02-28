<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Facades\Filesystem;
use Hyde\Foundation\Concerns\HydeExtension;
use Hyde\Foundation\Kernel\FileCollection;
use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Publications\Actions\GeneratesPublicationTagPages;
use Hyde\Publications\Models\PublicationListPage;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function range;
use function str_ends_with;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationsExtensionTest
 */
class PublicationsExtension extends HydeExtension
{
    /** @var \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationType> */
    protected static Collection $types;

    /** @return \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationType> */
    public static function getTypes(): Collection
    {
        self::constructTypesIfNotConstructed();

        return static::$types;
    }

    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public static function getPageClasses(): array
    {
        return [
            PublicationPage::class,
            PublicationListPage::class,
        ];
    }

    public static function discoverFiles(FileCollection $collection): void
    {
        // Todo refactor to handle file discovery here
    }

    public static function discoverPages(PageCollection $collection): void
    {
        static::discoverPublicationPages($collection);

        if (Filesystem::exists('tags.yml')) {
            static::generatePublicationTagPages($collection);
        }
    }

    protected static function discoverPublicationPages(PageCollection $instance): void
    {
        static::$types = new Collection(); // Reset if we are in a test environment
        static::$types = static::findPublicationTypes();
        static::$types->each(function (PublicationType $type) use ($instance): void {
            static::discoverPublicationPagesForType($type, $instance);
            static::generatePublicationListingPageForType($type, $instance);
        });
    }

    protected static function discoverPublicationPagesForType(PublicationType $type, PageCollection $instance): void
    {
        static::findPublicationsForType($type)->each(function (PublicationPage $publication) use (
            $instance
        ): void {
            $instance->addPage($publication);
        });
    }

    protected static function generatePublicationListingPageForType(PublicationType $type, PageCollection $instance): void
    {
        $page = new PublicationListPage($type);
        $instance->put($page->getSourcePath(), $page);

        if ($type->usesPagination()) {
            static::generatePublicationPaginatedListingPagesForType($type, $instance);
        }
    }

    protected static function generatePublicationPaginatedListingPagesForType(PublicationType $type,
        PageCollection $instance
    ): void {
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

    protected static function generatePublicationTagPages(PageCollection $collection): void
    {
        (new GeneratesPublicationTagPages($collection))->__invoke();
    }

    /** @return Collection<string, PublicationPage> */
    protected static function findPublicationTypes(): Collection
    {
        return Collection::make(static::getSchemaFiles())->mapWithKeys(function (string $schemaFile): array {
            $publicationType = PublicationType::fromFile(Hyde::pathToRelative($schemaFile));

            return [$publicationType->getDirectory() => $publicationType];
        });
    }

    /** @return Collection<int, PublicationPage> */
    protected static function findPublicationsForType(PublicationType $publicationType): Collection
    {
        return Collection::make(static::getPublicationFiles($publicationType->getDirectory()))->map(function (string $file): PublicationPage {
            return PublicationPage::parse(Str::before(Hyde::pathToRelative($file), PublicationPage::fileExtension()));
        });
    }

    protected static function getSchemaFiles(): array
    {
        return glob(Hyde::path(Hyde::getSourceRoot()).'/*/schema.json');
    }

    protected static function getPublicationFiles(string $directory): array
    {
        return glob(Hyde::path("$directory/*.md"));
    }

    /** @experimental This feature may be removed pending actual necessity,
     *               as the array would only be uninitialized when the kernel has not yet booted,
     *               a point which may actually be too early to actually interact with this domain.
     *               Nonetheless, it's present for compatability during the ongoing container refactor.
     */
    private static function constructTypesIfNotConstructed(): void
    {
        if (! isset(static::$types)) {
            static::$types = static::findPublicationTypes();
        }
    }

    /** @internal */
    public static function clearTypes(): void
    {
        static::$types = new Collection();
    }
}
