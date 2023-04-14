<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Hyde;
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

    public function __construct()
    {
        $this->tags = $this->parseTagsFile();
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
