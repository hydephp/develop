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
     *
     * @throws \Safe\Exceptions\FilesystemException
     */
    public static function parsePublicationFile(string $mdFileName): PublicationPage
    {
        $fileData = file_get_contents(Hyde::path($mdFileName));
        if (! $fileData) {
            throw new Exception("No data read from [$mdFileName]");
        }

        $parsedFileData = YamlFrontMatter::markdownCompatibleParse($fileData);
        return new PublicationPage(
            PublicationType::get(basename(dirname($mdFileName))),
            basename($mdFileName, '.md'),
            $parsedFileData->matter(),
            $parsedFileData->body()
        );
    }

    public static function publicationTypeExists(string $pubTypeName): bool
    {
        return self::getPublicationTypes()->has(Str::slug($pubTypeName));
    }
}
