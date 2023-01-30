<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\DataCollections\Facades;

use Hyde\Framework\Features\DataCollections\DataCollection;

/**
 * @see \Hyde\Framework\Testing\Feature\DataCollectionTest
 * @deprecated Since this class is so simple, it could easily be inlined.
 */
class MarkdownCollection
{
    public static function get(string $collectionKey): DataCollection
    {
        return DataCollection::markdown($collectionKey);
    }
}
