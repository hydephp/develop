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
     *
     * @param string $key for a subdirectory of the _data directory
     * @return DataCollection<\Hyde\Framework\Models\MarkdownDocument>
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
