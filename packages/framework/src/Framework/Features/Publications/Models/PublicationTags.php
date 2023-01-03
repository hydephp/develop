<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function file_exists;
use function file_get_contents;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Illuminate\Support\Collection;
use function json_decode;
use function json_encode;
use JsonException;

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

    /** @return \Illuminate\Support\Collection<string, array<string>> */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param  array<string>|string  $values
     * @return $this
     */
    public function addTagGroup(string $name, array|string $values): self
    {
        $this->tags->put($name, (array) $values);

        return $this;
    }

    /**
     * @param  array<string, array<string>|string>  $tags
     * @return $this
     */
    public function addTagGroups(array $tags): self
    {
        foreach ($tags as $name => $values) {
            $this->addTagGroup($name, $values);
        }

        return $this;
    }

    /**
     * @param  array<string>|string  $values
     * @return $this
     */
    public function addTagsToGroup(string $name, array|string $values): self
    {
        $this->tags->put($name, array_merge($this->tags->get($name, []), (array) $values));

        return $this;
    }

    /**
     * Save the tags collection to disk.
     *
     * @return $this
     */
    public function save(): self
    {
        Filesystem::putContents('tags.json', json_encode($this->tags, JSON_PRETTY_PRINT));

        return $this;
    }

    /**
     * Get all available tags.
     *
     * @return Collection<string, array<string>>
     */
    public static function getAllTags(): Collection
    {
        return (new self())->getTags()->sortKeys();
    }

    /**
     * Get all values for a given tag name.
     *
     * @return array<string>
     */
    public static function getValuesForTagName(string $tagName): array
    {
        return self::getAllTags()->get($tagName) ?? [];
    }

    /**
     * Validate the tags.json file is valid.
     *
     * @internal This method is experimental and may be removed without notice
     */
    public static function validateTagsFile(): void
    {
        if (! file_exists(Hyde::path('tags.json'))) {
            throw new FileNotFoundException('tags.json');
        }

        $tags = json_decode(file_get_contents(Hyde::path('tags.json')), true);

        if (! is_array($tags) || empty($tags)) {
            throw new JsonException('Could not decode tags.json');
        }

        foreach ($tags as $name => $values) {
            assert(is_string($name));
            assert(is_array($values));
            foreach ($values as $key => $value) {
                assert(is_int($key));
                assert(is_string($value));
            }
        }
    }

    /** @return array<string, array<string>> */
    protected function parseTagsFile(): array
    {
        if (file_exists(Hyde::path('tags.json'))) {
            return json_decode(file_get_contents(Hyde::path('tags.json')), true);
        }

        return [];
    }
}
