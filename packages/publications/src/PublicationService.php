<?php

declare(strict_types=1);

namespace Hyde\Publications;

use function glob;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\Models\PublicationType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function json_decode;
use function validator;

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
        return Collection::make(static::getSchemaFiles())->mapWithKeys(function (string $schemaFile): array {
            $publicationType = PublicationType::fromFile(Hyde::pathToRelative($schemaFile));

            return [$publicationType->getDirectory() => $publicationType];
        });
    }

    /**
     * Return all publications for a given publication type.
     */
    public static function getPublicationsForPubType(PublicationType $pubType): Collection
    {
        return Collection::make(static::getPublicationFiles($pubType->getDirectory()))->map(function (string $file): PublicationPage {
            return static::parsePublicationFile(Hyde::pathToRelative($file));
        })->sortBy(function (PublicationPage $page) use ($pubType): mixed {
            return $page->matter($pubType->sortField);
        }, descending: ! $pubType->sortAscending)->values();
    }

    /**
     * Return all media items for a given publication type.
     */
    public static function getMediaForPubType(PublicationType $pubType): Collection
    {
        return Collection::make(static::getMediaFiles($pubType->getDirectory()))->map(function (string $file): string {
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
     */
    public static function parsePublicationFile(string $identifier): PublicationPage
    {
        return PublicationPage::parse(Str::replaceLast('.md', '', $identifier));
    }

    /**
     * Check whether a given publication type exists.
     */
    public static function publicationTypeExists(string $pubTypeName): bool
    {
        return static::getPublicationTypes()->has(Str::slug($pubTypeName));
    }

    /**
     * Validate the schema.json file is valid.
     *
     * @internal This method is experimental and may be removed without notice
     */
    public static function validateSchemaFile(string $pubTypeName, bool $throw = true): array
    {
        $schema = json_decode(Filesystem::getContents("$pubTypeName/schema.json"));
        $errors = [];

        $schemaValidator = validator([
            'name' => $schema->name ?? null,
            'canonicalField' => $schema->canonicalField ?? null,
            'detailTemplate' => $schema->detailTemplate ?? null,
            'listTemplate' => $schema->listTemplate ?? null,
            'sortField' => $schema->sortField ?? null,
            'sortAscending' => $schema->sortAscending ?? null,
            'pageSize' => $schema->pageSize ?? null,
            'fields' => $schema->fields ?? null,
            'directory' => $schema->directory ?? null,
        ], [
            'name' => 'required|string',
            'canonicalField' => 'nullable|string',
            'detailTemplate' => 'nullable|string',
            'listTemplate' => 'nullable|string',
            'sortField' => 'nullable|string',
            'sortAscending' => 'nullable|boolean',
            'pageSize' => 'nullable|integer',
            'fields' => 'nullable|array',
            'directory' => 'nullable|prohibited',
        ]);

        $errors['schema'] = $schemaValidator->errors()->toArray();

        if ($throw) {
            $schemaValidator->validate();
        }

        // TODO warn if fields are empty?

        // TODO warn if canonicalField does not match meta field or actual?

        // TODO Warn if template files do not exist (assuming files not vendor views)?

        // TODO warn if pageSize is less than 0 (as that equals no pagination)?

        $errors['fields'] = [];

        foreach ($schema->fields as $field) {
            $fieldValidator = validator([
                'type' => $field->type ?? null,
                'name' => $field->name ?? null,
                'rules' => $field->rules ?? null,
                'tagGroup' => $field->tagGroup ?? null,
            ], [
                'type' => 'required|string',
                'name' => 'required|string',
                'rules' => 'nullable|array',
                'tagGroup' => 'nullable|string',
            ]);

            // TODO check tag group exists?

            $errors['fields'][] = $fieldValidator->errors()->toArray();

            if ($throw) {
                $fieldValidator->validate();
            }
        }

        return $errors;
    }

    protected static function getSchemaFiles(): array
    {
        return glob(Hyde::path(Hyde::getSourceRoot()).'/*/schema.json');
    }

    protected static function getPublicationFiles(string $directory): array
    {
        return glob(Hyde::path("$directory/*.md"));
    }

    protected static function getMediaFiles(string $directory, string $extensions = '{jpg,jpeg,png,gif,pdf}'): array
    {
        return glob(Hyde::path("_media/$directory/*.$extensions"), GLOB_BRACE);
    }
}
