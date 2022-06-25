<?php

namespace Hyde\Framework\Modules\DataCollections\Facades;

use Hyde\Framework\Modules\DataCollections\DataCollection;

class MarkdownCollection
{
    public static function get(string $collectionKey): DataCollection
    {
        return DataCollection::markdown($collectionKey);
    }
}
