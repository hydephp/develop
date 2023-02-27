<?php

declare(strict_types=1);

namespace Hyde\Publications;

use function glob;
use Hyde\Hyde;
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
     * @todo We might want to refactor to cache this in the Kernel, maybe under $publications?
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
        $publications = Hyde::pages()->getPages(PublicationPage::class)->values()->toBase();

        $sortAscending = $sortAscending !== null ? $sortAscending : $publicationType->sortAscending;
        $sortField = $sortField !== null ? $sortField : $publicationType->sortField;

        return $publications->sortBy(function (PublicationPage $page) use ($sortField): mixed {
            return $page->matter($sortField);
        }, descending: ! $sortAscending)->values();
    }

    /**
     * Return all media items for a given publication type.
     */
    public static function getMediaForType(PublicationType $publicationType): Collection
    {
        return Collection::make(static::getMediaFiles($publicationType->getDirectory()))->map(function (string $file): string {
            return Hyde::pathToRelative($file);
        });
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
     * Parse a publication Markdown source file and return a PublicationPage object.
     *
     * @param  string  $identifier  Example: my-publication/hello.md or my-publication/hello
     *
     * @deprecated Will be merged into called method
     */
    public static function parsePublicationFile(string $identifier): PublicationPage
    {
        return PublicationPage::parse(Str::replaceLast('.md', '', $identifier));
    }

    /**
     * Check whether a given publication type exists.
     */
    public static function publicationTypeExists(string $publicationTypeName): bool
    {
        return static::getPublicationTypes()->has(Str::slug($publicationTypeName));
    }

    protected static function getMediaFiles(string $directory, string $extensions = '{jpg,jpeg,png,gif,pdf}'): array
    {
        return glob(Hyde::mediaPath("$directory/*.$extensions"), GLOB_BRACE);
    }
}
