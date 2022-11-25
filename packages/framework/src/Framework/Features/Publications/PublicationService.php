<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use function basename;
use function dirname;
use Exception;
use function glob;
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
        return Collection::create(self::getSchemaFiles())->mapWithKeys(function (string $schemaFile): array {
            $publicationType = PublicationType::fromFile(Hyde::pathToRelative($schemaFile));

            return [$publicationType->getDirectory() => $publicationType];
        });
    }

    /**
     * Return all publications for a given pub type, optionally sorted by the publication's sortField.
     */
    public static function getPublicationsForPubType(PublicationType $pubType): Collection
    {
        $files = glob(Hyde::path("{$pubType->getDirectory()}/*.md"));

        return Collection::create($files)->map(function (string $file): PublicationPage {
            return self::parsePublicationFile(Hyde::pathToRelative($file));
        });
    }

    /**
     * Return all media items for a given publication type.
     */
    public static function getMediaForPubType(PublicationType $pubType): Collection
    {
        $files = glob(Hyde::path("_media/{$pubType->getDirectory()}/*").'.{jpg,jpeg,png,gif,pdf}', GLOB_BRACE);

        return Collection::create($files)->map(function (string $file): string {
            return Hyde::pathToRelative($file);
        });
    }

    /**
     * Parse a publication Markdown source file and return a PublicationPage object.
     *
     * @param  string  $identifier  Example: my-publication/hello.md or my-publication/hello
     */
    public static function parsePublicationFile(string $identifier): PublicationPage
    {
        $identifier = Str::replaceLast('.md', '', $identifier);
        $fileData = self::getPublicationFileData("$identifier.md");

        $parsedFileData = YamlFrontMatter::markdownCompatibleParse($fileData);

        return new PublicationPage(
            type:       PublicationType::get(dirname($identifier)),
            identifier: basename($identifier),
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
     * @throws \Exception If the file could not be read.
     */
    protected static function getPublicationFileData(string $filepath): string
    {
        $fileData = file_get_contents(Hyde::path($filepath));
        if (! $fileData) {
            throw new Exception("No data read from [$filepath]");
        }

        return $fileData;
    }

    protected static function getSchemaFiles(): array
    {
        return glob(Hyde::path(Hyde::getSourceRoot()).'/*/schema.json');
    }
}
