<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Hyde;

use Illuminate\Support\Collection;

use function file_exists;
use function file_get_contents;
use function json_decode;

/**
 * Object representation for the tags.json file.
 *
 * @see \Hyde\Framework\Testing\Feature\PublicationTagsTest
 */
class PublicationTags
{
    /**
     * Get all available tags.
     */
    public static function getAllTags(): Collection
    {
        return Collection::make(self::parseTagsFile())->sortKeys();
    }

    /**
     * Get all values for a given tag name.
     */
    public static function getValuesForTagName(string $tagName): Collection
    {
        return Collection::make(self::getAllTags()->get($tagName) ?? []);
    }

    protected static function parseTagsFile(): array
    {
        if (file_exists(Hyde::path('tags.json'))) {
            return json_decode(file_get_contents(Hyde::path('tags.json')), true);
        }

        return [];
    }
}
