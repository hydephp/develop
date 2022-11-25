<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Carbon\Carbon;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Illuminate\Support\Str;
use Rgasch\Collection\Collection;
use function Safe\file_get_contents;
use Spatie\YamlFrontMatter\YamlFrontMatter;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\PublicationServiceTest
 */
class PublicationService
{
    /**
     * Return a collection of all defined publication types, indexed by the directory name.
     *
     * @todo We might want to refactor to cache this in the Kernel, maybe under $publications?
     *
     * @return Collection<string, PublicationType>
     *
     * @throws \Exception
     */
    public static function getPublicationTypes(): Collection
    {
        $root = Hyde::path();
        $schemaFiles = glob("$root/*/schema.json", GLOB_BRACE);

        $pubTypes = Collection::create();
        foreach ($schemaFiles as $schemaFile) {
            $publicationType = PublicationType::fromFile($schemaFile);
            $pubTypes->{$publicationType->getDirectory()} = $publicationType;
        }

        return $pubTypes;
    }

    /**
     * Return all publications for a given pub type, optionally sorted by the publication's sortField.
     *
     * @throws \Safe\Exceptions\FilesystemException
     */
    public static function getPublicationsForPubType(PublicationType $pubType, $sort = true): Collection
    {
        $root = Hyde::path();
        $files = glob("$root/{$pubType->getDirectory()}/*.md");

        $publications = Collection::create();
        foreach ($files as $file) {
            $publications->add(self::getPublicationData($file));
        }

        if ($sort === true) {
            return $publications->sortBy(fn(PublicationPage $publication): string|int|null => $publication->matter->{$pubType->sortField});
        }

        return $publications;
    }

    /**
     * Return all media items for a given publication type.
     */
    public static function getMediaForPubType(PublicationType $pubType, $sort = true): Collection
    {
        $root = Hyde::path();
        $files = glob("$root/_media/{$pubType->getDirectory()}/*.{jpg,jpeg,png,gif,pdf}", GLOB_BRACE);

        $media = Collection::create();
        foreach ($files as $file) {
            $media->add($file);
        }

        if ($sort) {
            return $media->sort()->values();
        }

        return $media;
    }

    /**
     * Read an MD file and return the parsed data.
     *
     * @throws \Safe\Exceptions\FilesystemException
     */
    public static function getPublicationData(string $mdFileName): PublicationPage
    {
        $fileData = file_get_contents($mdFileName);
        if (! $fileData) {
            throw new \Exception("No data read from [$mdFileName]");
        }

        $parsedFileData = YamlFrontMatter::markdownCompatibleParse($fileData);
        $matter = $parsedFileData->matter();
        $markdown = $parsedFileData->body();
        $matter['__slug'] = basename($mdFileName, '.md');
        $matter['__createdDatetime'] = Carbon::createFromTimestamp($matter['__createdAt']);

        $type = PublicationType::get(basename(dirname($mdFileName)));

        $identifier = basename($mdFileName, '.md');

        return new PublicationPage($type, $identifier, $matter, $markdown);
    }

    /**
     * Check whether a given publication type exists.
     *
     * @throws \Exception
     */
    public static function publicationTypeExists(string $pubTypeName, bool $isRaw = true): bool
    {
        if ($isRaw) {
            $pubTypeName = Str::slug($pubTypeName);
        }

        return self::getPublicationTypes()->has($pubTypeName);
    }
}
