<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Publications\Pages\PublicationPage;
use Hyde\Publications\Concerns\PublicationFieldTypes;

use function array_merge;
use function array_unique;

/**
 * Facade for the tags used in publication pages.
 *
 * @see \Hyde\Publications\Testing\Feature\PublicationTagsTest
 */
class PublicationTags
{
    /**
     * Get all available tags used in the project's publications.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        $tags = [];

        /** @var PublicationPage $page */
        foreach (PublicationPage::all() as $page) {
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
}
