<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Carbon\Carbon;
use Exception;
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
     */
    public static function getPublicationTypes(): Collection
    {
        $root = Hyde::path();
        $schemaFiles = glob("$root/*/schema.json");

        return Collection::create($schemaFiles)->mapWithKeys(function (string $schemaFile): array {
            $publicationType = PublicationType::fromFile($schemaFile);
            return [$publicationType->getDirectory() => $publicationType];
        });
    }

    /**
     * Return all publications for a given pub type, optionally sorted by the publication's sortField.
     */
    public static function getPublicationsForPubType(PublicationType $pubType): Collection
    {
        $root = Hyde::path();
        $files = glob("$root/{$pubType->getDirectory()}/*.md");

        $publications = Collection::create();
        foreach ($files as $file) {
            $publications->add(self::parsePublicationFile(Hyde::pathToRelative($file)));
        }

        return $publications;
    }

    /**
     * Return all media items for a given publication type.
     */
    public static function getMediaForPubType(PublicationType $pubType): Collection
    {
        $root = Hyde::path();
        $files = glob("$root/_media/{$pubType->getDirectory()}/*.{jpg,jpeg,png,gif,pdf}", GLOB_BRACE);

        $media = Collection::create();
        foreach ($files as $file) {
            $media->add($file);
        }

        return $media;
    }

    /**
     * Read an MD file and return the parsed data.
     *
     * @param  string  $mdFileName  Example: my-publication/hello.md
     */
    public static function parsePublicationFile(string $mdFileName): PublicationPage
    {
        $fileData = self::getPublicationFileData($mdFileName);

        $parsedFileData = YamlFrontMatter::markdownCompatibleParse($fileData);
        return new PublicationPage(
            type:       PublicationType::get(dirname($mdFileName)),
            identifier: basename($mdFileName, '.md'),
            matter:     $parsedFileData->matter(),
            markdown:   $parsedFileData->body()
        );
    }

    /**
     * Check whether a given publication type exists.
     */
    public static function publicationTypeExists(string $pubTypeName): bool
    {
        return self::getPublicationTypes()->has(Str::slug($pubTypeName));
    }

    /**
     * @throws \Safe\Exceptions\FilesystemException
     */
    protected static function getPublicationFileData(string $mdFileName): string
    {
        $fileData = file_get_contents(Hyde::path($mdFileName));
        if (!$fileData) {
            throw new Exception("No data read from [$mdFileName]");
        }
        return $fileData;
    }
}
