<?php

declare(strict_types=1);

namespace Hyde\Publications;

use Hyde\Hyde;
use Hyde\Foundation\Facades\Files;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\Models\PublicationType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationServiceTest
 */
class PublicationService
{
    /**
     * Return a collection of all defined publication types, indexed by the directory name.
     *
     * @return Collection<string, PublicationType>
     */
    public static function getPublicationTypes(): Collection
    {
        return PublicationsExtension::getTypes();
    }

    /**
     * Return all publications for a given publication type.
     *
     * @return Collection<int, PublicationPage>
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
        return Files::getMediaFiles()->filter(function (MediaFile $file) use ($publicationType): bool {
            return Str::startsWith($file->getPath(), Hyde::getMediaDirectory().'/'.$publicationType->getDirectory());
        })->map(function (MediaFile $file): string {
            return $file->getPath();
        })->values()->toBase();
    }

    /**
     * Get all available tags.
     */
    public static function getAllTags(): Collection
    {
        return PublicationTags::getAllTags();
    }

    /**
     * Get all values for a given tag name.
     */
    public static function getValuesForTagName(string $tagName): Collection
    {
        return collect(PublicationTags::getValuesForTagName($tagName));
    }

    /**
     * Check whether a given publication type exists.
     */
    public static function publicationTypeExists(string $publicationTypeName): bool
    {
        return static::getPublicationTypes()->has(Str::slug($publicationTypeName));
    }
}
