<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Publications\Concerns\PublicationFieldTypes;

use function file_exists;
use function array_merge;
use function array_unique;

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
        $tags = [];
        $pages = PublicationPage::all();

        /** @var PublicationPage $page */
        foreach ($pages as $page) {
            // We need to get the schema, so that we know which front matter fields are tags.
            $schema = $page->getType()->getFields();

            foreach ($schema as $field) {
                if ($field->type === PublicationFieldTypes::Tag) {
                    $tags = array_merge($tags, (array) $page->matter($field->name));
                }
            }
        }

        // Todo this is an excellent place to count the number of times a tag is used.

        return array_values(array_unique($tags));
    }

    /**
     * Get all available tags.
     *
     * @deprecated Use the `all()` method instead.
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
