<?php

namespace Hyde\Framework\Modules\DataCollections\Facades;

use Hyde\Framework\Modules\DataCollections\DataCollection;

/**
 * @see \Hyde\Framework\Testing\Modules\DataCollections\DataCollectionTest\DataCollectionTest
 */
class MarkdownCollection
{
    public static function get(string $collectionKey): DataCollection
    {
        return DataCollection::markdown($collectionKey);
    }
}
