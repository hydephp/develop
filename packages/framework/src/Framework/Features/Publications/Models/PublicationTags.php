<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function file_exists;
use function file_get_contents;
use Hyde\Hyde;
use Illuminate\Support\Collection;
use function json_decode;

/**
 * Object representation for the tags.json file.
 *
 * @see \Hyde\Framework\Testing\Feature\PublicationTagsTest
 */
class PublicationTags
{
    /** @var Collection<string, array<string>> */
    protected Collection $tags;

    public function __construct()
    {
        $this->tags = Collection::make($this->parseTagsFile());
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(string $name, array $values): self
    {
        $this->tags->put($name, $values);

        return $this;
    }

    /**
     * Get all available tags.
     */
    public static function getAllTags(): Collection
    {
        return (new self())->getTags()->sortKeys();
    }

    /**
     * Get all values for a given tag name.
     */
    public static function getValuesForTagName(string $tagName): Collection
    {
        return Collection::make(self::getAllTags()->get($tagName) ?? []);
    }

    protected function parseTagsFile(): array
    {
        if (file_exists(Hyde::path('tags.json'))) {
            return json_decode(file_get_contents(Hyde::path('tags.json')), true);
        }

        return [];
    }
}
