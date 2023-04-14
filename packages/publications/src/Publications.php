<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Support\Filesystem\MediaFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function collect;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationServiceTest
 */
class Publications
{
    /**
     * Return a collection of all defined publication types, indexed by the directory name.
     *
     * @return Collection<string, PublicationType>
     */
    public static function getPublicationTypes(): Collection
    {
        return Hyde::kernel()->getExtension(PublicationsExtension::class)->getTypes();
    }

    /**
     * Return all publications for a given publication type.
     *
     * @return Collection<int, \Hyde\Publications\Pages\PublicationPage>
     */
    public static function getPublicationsForType(PublicationType $publicationType, ?string $sortField = null, ?bool $sortAscending = null): Collection
    {
        $publications = Hyde::pages()->getPages(PublicationPage::class);

        $sortAscending = $sortAscending !== null ? $sortAscending : $publicationType->sortAscending;
        $sortField = $sortField !== null ? $sortField : $publicationType->sortField;

        return $publications->sortBy(function (PublicationPage $page) use ($sortField): mixed {
            return $page->matter($sortField);
        }, descending: ! $sortAscending)->values()->toBase();
    }

    /**
     * Return all media items for a given publication type.
     */
    public static function getMediaForType(PublicationType $publicationType): Collection
    {
        return collect(MediaFile::all())->filter(function (MediaFile $file) use ($publicationType): bool {
            return Str::startsWith($file->getPath(), Hyde::getMediaDirectory().'/'.$publicationType->getDirectory());
        })->keys()->toBase();
    }

    /**
     * Get all available tags used in the publications.
     *
     * The tags are aggregated from the front matter of all publication pages, where the field type is "tag".
     */
    public static function getPublicationTags(): array
    {
        return PublicationTags::all();
    }

    /**
     * Check whether a given publication type exists.
     */
    public static function publicationTypeExists(string $publicationTypeName): bool
    {
        return static::getPublicationTypes()->has(Str::slug($publicationTypeName));
    }
}
