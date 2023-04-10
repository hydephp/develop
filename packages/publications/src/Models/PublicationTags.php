<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Symfony\Component\Yaml\Yaml;

use function file_exists;
use function array_merge;

/**
 * Object representation for the tags.yml file, as well as a static facade helper.
 *
 * @see \Hyde\Publications\Testing\Feature\PublicationTagsTest
 */
class PublicationTags
{
    /** @var array<string> */
    protected array $tags;

    /**
     * Get all available tags used in the project's publications.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        //
    }

    /**
     * Get all available tags.
     *
     * @return array<string>
     */
    public static function getAllTags(): array
    {
        return (new self())->getTags();
    }

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
        $this->tags = array_merge($this->tags, (array) $values);

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

    /** @return array<string> */
    protected function parseTagsFile(): array
    {
        if (file_exists(Hyde::path('tags.yml'))) {
            return Yaml::parseFile(Hyde::path('tags.yml'));
        }

        return [];
    }
}
