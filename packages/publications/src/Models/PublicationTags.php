<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Symfony\Component\Yaml\Yaml;

use function file_exists;

/**
 * Object representation for the tags.yml file.
 *
 * @see \Hyde\Publications\Testing\Feature\PublicationTagsTest
 */
class PublicationTags
{
    /** @var array<string> */
    protected array $tags;

    public function __construct()
    {
        $this->tags = $this->parseTagsFile();
    }

    /** @return \Illuminate\Support\array<string> */
    public function getTags(): array
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
        $this->tags = collect($this->tags)->merge((array) $values)->all();

        return $this;
    }

    /**
     * Save the tags array to disk.
     *
     * @return $this
     */
    public function save(): self
    {
        Filesystem::putContents('tags.yml', Yaml::dump($this->tags));

        return $this;
    }

    /**
     * Get all available tags.
     *
     * @return array<string>
     */
    public static function getAllTags(): array
    {
        return collect((new self())->getTags())->sortKeys()->all();
    }

    /** @return array<string> */
    protected function parseTagsFile(): array
    {
        if (file_exists(Hyde::path('tags.yml'))) {
            return Yaml::parseFile(Hyde::path('tags.yml'));
        }

        return [];
    }
}
