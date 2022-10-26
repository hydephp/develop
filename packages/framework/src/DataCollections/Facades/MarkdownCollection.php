<?php

declare(strict_types=1);

namespace Hyde\DataCollections\Facades;

use Hyde\DataCollections\DataCollection;

/**
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 */
class MarkdownCollection
{
    public static function get(string $collectionKey): DataCollection
    {
        return DataCollection::markdown($collectionKey);
    }
}
