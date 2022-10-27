<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\DataCollections\Facades;

use Hyde\Framework\Features\DataCollections\DataCollection;

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
