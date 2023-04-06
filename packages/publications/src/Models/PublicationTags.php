<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Collection;

use function file_exists;

/**
 * Object representation for the tags.yml file.
 *
 * @see \Hyde\Publications\Testing\Feature\PublicationTagsTest
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
     * Add one or more tags to the collection.
     *
     * @param  array<string>|string  $values
     * @return $this
     */
    public function addTags(array|string $values): self
    {
        $this->tags = $this->tags->merge((array) $values);

        return $this;
    }

    /**
     * @deprecated Tag groups are being removed, in favour of a flat list array of tags.
     *
     * @param  array<string>|string  $values
     * @return $this
     */
    public function addTagGroup(string $name, array|string $values): self
    {
        $this->tags->put($name, (array) $values);

        return $this;
    }

    /**
     * @deprecated Tag groups are being removed, in favour of a flat list array of tags.
     *
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
     * Save the tags collection to disk.
     *
     * @return $this
     */
    public function save(): self
    {
        Filesystem::putContents('tags.yml', Yaml::dump($this->tags->toArray()));

        return $this;
    }

    /**
     * Get all available tags, arranged by their tag group.
     *
     * @return Collection<string, array<string>>
     */
    public static function getAllTags(): Collection
    {
        return (new self())->getTags()->sortKeys();
    }

    /** @return array<string, array<string>> */
    protected function parseTagsFile(): array
    {
        if (file_exists(Hyde::path('tags.yml'))) {
            return Yaml::parseFile(Hyde::path('tags.yml'));
        }

        return [];
    }
}
