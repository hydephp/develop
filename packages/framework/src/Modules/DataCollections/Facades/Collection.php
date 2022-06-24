<?php

namespace Hyde\Framework\Modules\DataCollections\Facades;

use Hyde\Framework\Modules\DataCollections\DataCollection;
use Hyde\Framework\Services\MarkdownFileService;

/**
 * Provides a facade for generating on-the-fly Laravel Collections
 * from static data files such as Markdown components and YAML files.
 */
class Collection
{
    /**
     * Get a collection of Markdown documents in the _data/<$key> directory.
     * Each Markdown file will be parsed into a MarkdownDocument with front matter.
     */
    public static function markdown(string $key): DataCollection
    {
        $collection = new DataCollection($key);
        foreach ($collection->getMarkdownFiles() as $file) {
            $collection->push(
                (new MarkdownFileService($file))->get()
            );
        }
        return $collection->getCollection();
    }
}
